<?php

namespace Jiny\Auth\App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use PragmaRX\Google2FA\Google2FA;
use App\Models\User;

class Login2FAController extends Controller
{
    private $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * 2FA 인증 페이지 표시
     * GET /login/2fa
     */
    public function index()
    {
        // 세션에 임시 사용자 ID가 없는 경우
        if (!Session::has('2fa_user_id')) {
            return redirect()->route('login')
                ->with('error', '잘못된 접근입니다.');
        }
        
        return view('jiny-auth::auth.login-2fa');
    }

    /**
     * 2FA 인증 처리
     * POST /login/2fa/verify
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required',
        ]);
        
        // 세션에서 사용자 ID 가져오기
        $userId = Session::get('2fa_user_id');
        
        if (!$userId) {
            return redirect()->route('login')
                ->with('error', '세션이 만료되었습니다. 다시 로그인해주세요.');
        }
        
        $user = User::find($userId);
        
        if (!$user || !$user->two_factor_secret) {
            return redirect()->route('login')
                ->with('error', '2단계 인증이 설정되지 않았습니다.');
        }
        
        // 일반 인증 코드 확인 (6자리)
        if (strlen($request->code) === 6) {
            $secret = decrypt($user->two_factor_secret);
            $valid = $this->google2fa->verifyKey($secret, $request->code);
            
            if ($valid) {
                // 세션 정리
                Session::forget('2fa_user_id');
                Session::forget('2fa_remember');
                
                // Remember Me 처리
                $remember = Session::get('2fa_remember', false);
                
                // 로그인 처리
                Auth::login($user, $remember);
                
                // 세션 재생성
                $request->session()->regenerate();
                
                // 로그인 성공 기록
                $this->logSuccess($user, $request);
                
                return redirect()->intended('/home');
            }
        }
        
        // 복구 코드 확인
        $recoveryCodes = DB::table('two_factor_recovery_codes')
            ->where('user_id', $user->id)
            ->whereNull('used_at')
            ->get();
        
        foreach ($recoveryCodes as $recoveryCode) {
            if (password_verify($request->code, $recoveryCode->code)) {
                // 복구 코드 사용 처리
                DB::table('two_factor_recovery_codes')
                    ->where('id', $recoveryCode->id)
                    ->update(['used_at' => now()]);
                
                // 세션 정리
                Session::forget('2fa_user_id');
                Session::forget('2fa_remember');
                
                // Remember Me 처리
                $remember = Session::get('2fa_remember', false);
                
                // 로그인 처리
                Auth::login($user, $remember);
                
                // 세션 재생성
                $request->session()->regenerate();
                
                // 로그인 성공 기록
                $this->logSuccess($user, $request);
                
                return redirect()->intended('/home')
                    ->with('warning', '복구 코드를 사용했습니다. 보안을 위해 새로운 복구 코드를 생성하세요.');
            }
        }
        
        // 인증 실패 기록
        $this->logFailure($user, $request);
        
        return back()->withErrors([
            'code' => '인증 코드가 올바르지 않습니다.'
        ]);
    }

    /**
     * 로그인 성공 기록
     */
    private function logSuccess($user, $request)
    {
        if (\Illuminate\Support\Facades\Schema::hasTable('user_logs')) {
            DB::table('user_logs')->insert([
                'user_id' => $user->id,
                'action' => 'login_2fa_success',
                'description' => '2FA 인증 성공',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now()
            ]);
        }
    }

    /**
     * 인증 실패 기록
     */
    private function logFailure($user, $request)
    {
        if (\Illuminate\Support\Facades\Schema::hasTable('user_logs')) {
            DB::table('user_logs')->insert([
                'user_id' => $user->id,
                'action' => 'login_2fa_failed',
                'description' => '2FA 인증 실패',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now()
            ]);
        }
    }

    /**
     * 2FA 취소 (로그인 화면으로 돌아가기)
     * GET /login/2fa/cancel
     */
    public function cancel()
    {
        // 세션 정리
        Session::forget('2fa_user_id');
        Session::forget('2fa_remember');
        
        return redirect()->route('login')
            ->with('info', '로그인이 취소되었습니다.');
    }
}