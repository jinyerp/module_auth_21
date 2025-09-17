<?php

namespace Jiny\Auth\App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Jiny\Auth\App\Models\Account;
use Carbon\Carbon;

/**
 * AuthLoginController
 *
 * 로그인 폼 표시 및 관련 기능을 담당하는 컨트롤러
 *
 * @package Jiny\Auth\App\Http\Controllers\Auth
 * @author JinyPHP
 * @version 1.0.0
 * @since 1.0.0
 * @license MIT
 *
 * 🔄 관련 테스트:
 * - test_login_enabled_disabled
 * - test_dynamic_configuration_reflection
 */
class AuthLoginController extends Controller
{
    private $config;
    private $viewFile;

    public function __construct()
    {
        $this->config = config('jiny-auth', [
            'login' => ['enabled' => true],
            'security' => [
                'captcha' => ['apply_to_login' => false],
                'two_factor' => ['enabled' => false],
                'ip_security' => ['enabled' => false]
            ],
            'auth' => ['login' => []]
        ]);
        $this->viewFile = 'jiny-auth::auth.login';
    }

    /**
     * 로그인 기능 활성화 확인
     *
     * @return \Illuminate\View\View|false
     */
    private function checkLoginEnable()
    {
        // 로그인 기능이 비활성화된 경우 확인
        $enabled = $this->config['login']['enabled'] ?? true;

        // 문자열로 저장된 경우 boolean으로 변환
        if (is_string($enabled)) {
            $enabled = filter_var($enabled, FILTER_VALIDATE_BOOLEAN);
        }

        if (!$enabled) {
            return view('jiny-auth::auth.disabled');
        }

        return false; // 통과
    }

    /**
     * 로그인 폼 표시
     *
     * 다양한 분기 처리:
     * 1. 로그인 기능 활성화/비활성화 확인
     * 2. 로그인 차단 상태 확인
     * 3. IP 화이트리스트/블랙리스트 확인
     * 4. CAPTCHA 설정 확인
     * 5. 2FA 설정 확인
     */
    public function index()
    {
        // 1. 로그인 기능 활성화 확인
        if($res = $this->checkLoginEnable()) {
            return $res;
        }

        // 2. 로그인 시도 제한 확인
        if ($this->isLoginBlocked()) {
            return view('jiny-auth::auth.login.disabled', [
                'message' => '로그인이 일시적으로 차단되었습니다. 잠시 후 다시 시도해주세요.'
            ]);
        }

        // 3. IP 화이트리스트/블랙리스트 확인
        if (!$this->checkIpAccess()) {
            return view('jiny-auth::auth.login.disabled', [
                'message' => '접근이 허용되지 않은 IP 주소입니다.'
            ]);
        }

        // 4. CAPTCHA 설정 확인
        $captchaEnabled = $this->config['security']['captcha']['apply_to_login'] ?? false;

        // 5. 2FA 설정 확인
        $twoFactorEnabled = $this->config['security']['two_factor']['enabled'] ?? false;

        // 로그인 폼 표시 (설정 정보와 함께)
        return view($this->viewFile, [
            'captcha_enabled' => $captchaEnabled,
            'two_factor_enabled' => $twoFactorEnabled,
            'login_settings' => $this->config['auth']['login'] ?? [],
        ]);
    }

    /**
     * 로그인 차단 확인
     */
    protected function isLoginBlocked()
    {
        $blockKey = 'login_blocked';
        return Cache::has($blockKey);
    }

    /**
     * IP 접근 권한 확인
     */
    protected function checkIpAccess()
    {
        $ipSecurity = $this->config['security']['ip_security'] ?? [];

        // IP 보안이 비활성화된 경우 통과
        if (!($ipSecurity['enabled'] ?? false)) {
            return true;
        }

        $currentIp = request()->ip();

        // IP 블랙리스트 확인
        $blacklistEnabled = $ipSecurity['blacklist_enabled'] ?? false;
        if ($blacklistEnabled) {
            $blacklistIps = $ipSecurity['blacklist_ips'] ?? [];
            if (in_array($currentIp, $blacklistIps)) {
                Log::warning('Blocked IP attempt to access login', ['ip' => $currentIp]);
                return false;
            }
        }

        // IP 화이트리스트 확인
        $whitelistEnabled = $ipSecurity['whitelist_enabled'] ?? false;
        if ($whitelistEnabled) {
            $whitelistIps = $ipSecurity['whitelist_ips'] ?? [];
            if (!in_array($currentIp, $whitelistIps)) {
                Log::warning('IP not in whitelist', ['ip' => $currentIp]);
                return false;
            }
        }

        return true;
    }

    /**
     * 로그인 처리
     */
    public function login(Request $request)
    {
        // 디버깅을 위한 로그
        Log::info('Login attempt', ['email' => $request->email]);
        
        // 유효성 검사
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Remember Me 옵션
        $remember = $request->boolean('remember');

        // 사용자 확인 (2FA 체크를 위해)
        $user = \App\Models\User::where('email', $credentials['email'])->first();
        
        Log::info('User lookup result', [
            'email' => $credentials['email'],
            'user_found' => $user ? 'yes' : 'no',
            'user_id' => $user ? $user->id : null
        ]);
        
        if ($user && Hash::check($credentials['password'], $user->password)) {
            Log::info('Password check passed', ['user_id' => $user->id]);
            
            // 2FA가 활성화된 경우
            if ($user->two_factor_secret && $user->two_factor_enabled) {
                // 세션에 임시 사용자 정보 저장
                Session::put('2fa_user_id', $user->id);
                Session::put('2fa_remember', $remember);
                
                Log::info('2FA enabled, redirecting to 2FA page', ['user_id' => $user->id]);
                // 2FA 인증 페이지로 리다이렉트
                return redirect()->route('login.2fa');
            }
            
            // 2FA가 없는 경우 일반 로그인 처리
            Auth::login($user, $remember);
            $request->session()->regenerate();

            // 로그인 성공 로그
            Log::info('User logged in successfully', [
                'user' => $user->email,
                'auth_check' => Auth::check(),
                'auth_id' => Auth::id()
            ]);

            // 로그인 후 리다이렉트
            Log::info('Redirecting to /home');
            return redirect()->intended('/home');
        }

        Log::warning('Login failed', [
            'email' => $credentials['email'],
            'user_found' => $user ? 'yes' : 'no',
            'password_check' => $user ? 'failed' : 'N/A'
        ]);
        
        // 로그인 실패
        return back()->withErrors([
            'email' => '이메일 또는 비밀번호가 일치하지 않습니다.',
        ])->onlyInput('email');
    }
}
