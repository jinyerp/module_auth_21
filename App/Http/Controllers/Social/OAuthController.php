<?php
namespace Jiny\Auth\App\Http\Controllers\Social;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use Laravel\Socialite\Facades\Socialite;

class OAuthController extends Controller
{
    public $setting=[];

    public function __construct()
    {
        $this->setting = config("jiny.auth.setting");
    }

    /**
     * 소셜 로그인 리디렉트
     * GET /login/{provider}
     */
    public function redirect(Request $request, $provider)
    {
        // 공급자 활성화 확인
        if (!$this->isProviderEnabled($provider)) {
            return redirect()->route('login')
                ->with('error', '해당 소셜 로그인은 지원되지 않습니다.');
        }
        
        try {
            return Socialite::driver($provider)->redirect();
        } catch (\Exception $e) {
            $this->logSocialLogin(null, $provider, null, null, 'failed', 'error', $e->getMessage());
            
            return redirect()->route('login')
                ->with('error', '소셜 로그인 서비스에 연결할 수 없습니다.');
        }
    }

    /**
     * 소셜 로그인 콜백
     * GET /login/{provider}/callback
     */
    public function callback(Request $request, $provider)
    {
        // 공급자 활성화 확인
        if (!$this->isProviderEnabled($provider)) {
            return redirect()->route('login')
                ->with('error', '해당 소셜 로그인은 지원되지 않습니다.');
        }
        
        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            $this->logSocialLogin(null, $provider, null, null, 'failed', 'error', $e->getMessage());
            
            return redirect()->route('login')
                ->with('error', '소셜 로그인에 실패했습니다.');
        }
        
        return $this->handleSocialUser($provider, $socialUser);
    }

    /**
     * 소셜 사용자 처리
     */
    protected function handleSocialUser($provider, $socialUser)
    {
        // 기존 소셜 계정 확인
        $socialAccount = DB::table('social_accounts')
            ->where('provider', $provider)
            ->where('provider_user_id', $socialUser->getId())
            ->first();
        
        if ($socialAccount) {
            // 기존 사용자 로그인
            $user = User::find($socialAccount->user_id);
            
            if (!$user) {
                return redirect()->route('login')
                    ->with('error', '사용자 계정을 찾을 수 없습니다.');
            }
            
            // 계정 상태 확인
            if (!$user->is_active) {
                $this->logSocialLogin($user->id, $provider, $socialUser->getId(), $socialUser->getEmail(), 'failed', 'error', '비활성화된 계정');
                return redirect()->route('login')
                    ->with('error', '계정이 비활성화되었습니다.');
            }
            
            // 토큰 업데이트
            $this->updateSocialAccount($socialAccount->id, $socialUser);
            
            // 로그인 처리
            Auth::login($user);
            $this->logSocialLogin($user->id, $provider, $socialUser->getId(), $socialUser->getEmail(), 'login', 'success');
            
            return redirect()->intended(route('home'));
        }
        
        // 이메일로 기존 사용자 확인
        $user = User::where('email', $socialUser->getEmail())->first();
        
        if ($user) {
            // 기존 사용자에 소셜 계정 연결
            if (Auth::check() && Auth::id() === $user->id) {
                // 로그인된 상태에서 연결
                $this->createSocialAccount($user->id, $provider, $socialUser);
                $this->logSocialLogin($user->id, $provider, $socialUser->getId(), $socialUser->getEmail(), 'connect', 'success');
                
                return redirect()->route('home.profile.social')
                    ->with('success', ucfirst($provider) . ' 계정이 연결되었습니다.');
            } else {
                // 로그인되지 않은 상태
                return redirect()->route('login')
                    ->with('error', '이미 등록된 이메일입니다. 비밀번호로 로그인해주세요.');
            }
        }
        
        // 신규 사용자 생성
        $user = $this->createUserFromSocialite($provider, $socialUser);
        
        // 소셜 계정 연결
        $this->createSocialAccount($user->id, $provider, $socialUser);
        
        // 로그인 처리
        Auth::login($user);
        $this->logSocialLogin($user->id, $provider, $socialUser->getId(), $socialUser->getEmail(), 'register', 'success');
        
        return redirect()->route('home')
            ->with('success', '회원가입이 완료되었습니다.');
    }
    
    /**
     * 공급자 활성화 확인
     */
    protected function isProviderEnabled($provider)
    {
        $validProviders = ['google', 'facebook', 'github', 'naver', 'kakao'];
        
        if (!in_array($provider, $validProviders)) {
            return false;
        }
        
        $providerConfig = DB::table('oauth_providers')
            ->where('name', $provider)
            ->where('enabled', true)
            ->first();
        
        return $providerConfig !== null;
    }
    
    /**
     * Socialite 데이터로 사용자 생성
     */
    protected function createUserFromSocialite($provider, $socialUser)
    {
        $user = User::create([
            'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'User',
            'email' => $socialUser->getEmail() ?? $provider . '_' . $socialUser->getId() . '@social.local',
            'email_verified_at' => now(),
            'password' => Hash::make(Str::random(16)),
            'avatar' => $socialUser->getAvatar(),
            'is_active' => true,
        ]);
        
        return $user;
    }
    
    /**
     * 소셜 계정 생성
     */
    protected function createSocialAccount($userId, $provider, $socialUser)
    {
        DB::table('social_accounts')->insert([
            'user_id' => $userId,
            'provider' => $provider,
            'provider_user_id' => $socialUser->getId(),
            'name' => $socialUser->getName() ?? $socialUser->getNickname(),
            'email' => $socialUser->getEmail(),
            'avatar' => $socialUser->getAvatar(),
            'access_token' => $socialUser->token ?? null,
            'refresh_token' => $socialUser->refreshToken ?? null,
            'token_expires_at' => isset($socialUser->expiresIn) ? now()->addSeconds($socialUser->expiresIn) : null,
            'provider_data' => json_encode($socialUser->user ?? []),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
    
    /**
     * 소셜 계정 업데이트
     */
    protected function updateSocialAccount($socialAccountId, $socialUser)
    {
        DB::table('social_accounts')
            ->where('id', $socialAccountId)
            ->update([
                'name' => $socialUser->getName() ?? $socialUser->getNickname(),
                'email' => $socialUser->getEmail(),
                'avatar' => $socialUser->getAvatar(),
                'access_token' => $socialUser->token ?? null,
                'refresh_token' => $socialUser->refreshToken ?? null,
                'token_expires_at' => isset($socialUser->expiresIn) ? now()->addSeconds($socialUser->expiresIn) : null,
                'provider_data' => json_encode($socialUser->user ?? []),
                'updated_at' => now(),
            ]);
    }
    
    /**
     * 소셜 로그인 로그 기록
     */
    protected function logSocialLogin($userId, $provider, $providerUserId, $email, $action, $status, $errorMessage = null)
    {
        DB::table('social_login_logs')->insert([
            'user_id' => $userId,
            'provider' => $provider,
            'provider_user_id' => $providerUserId,
            'email' => $email,
            'action' => $action,
            'status' => $status,
            'error_message' => $errorMessage,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);
    }
}