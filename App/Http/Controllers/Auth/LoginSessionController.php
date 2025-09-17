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
use Illuminate\Support\Facades\Route;
use Jiny\Auth\App\Models\Account;
use Jiny\Auth\App\Models\UserPasswordError;
use Carbon\Carbon;

/**
 * LoginSessionController
 *
 * 로그인 처리 및 세션 관리를 담당하는 컨트롤러
 *
 * @package Jiny\Auth\App\Http\Controllers\Auth
 * @author JinyPHP
 * @version 1.0.0
 * @since 1.0.0
 * @license MIT
 *
 * 🔄 관련 테스트:
 * - test_login_attempt_limitation
 * - test_successful_login_process
 * - test_ajax_login_process
 * - test_all_login_attempts_blocked_when_disabled
 */
class LoginSessionController extends Controller
{
    /**
     * 로그인 처리 메인 진입점
     *
     * 호출 구조:
     * 1. store() (현재 메소드)
     *    ├── 1-1. 로그인 기능 활성화 확인
     *    ├── 1-2. AJAX 요청 확인
     *    │   ├── 1-2.1. AJAX 요청인 경우: loginAjax() 호출
     *    │   └── 1-2.2. 일반 요청인 경우: 일반 로그인 처리
     *    ├── 1-3. 입력 데이터 검증
     *    ├── 1-4. 로그인 시도
     *    ├── 1-5. 성공 시 처리
     *    └── 1-6. 실패 시 처리
     */
    public function store(Request $request)
    {
        // 1-1: 로그인 기능 활성화 확인
        if (!$this->isLoginEnabled()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => '로그인 기능이 비활성화되었습니다.'
                ], 403);
            }

            return redirect()->route('login')
                ->with('error', '로그인 기능이 비활성화되었습니다.');
        }

        // 1-2: AJAX 요청 확인
        if ($request->ajax() || $request->wantsJson()) {
            return $this->loginAjax($request);
        }

        // 1-3: 일반 폼 제출 처리
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // 1-3.1: 로그인 시도 제한 확인 (새로운 비밀번호 오류 제한 기능)
        $loginAttemptCheck = $this->checkLoginAttemptsWithPasswordErrors($credentials['email'], $request);
        if (!$loginAttemptCheck['allowed']) {
            return back()->withErrors([
                'email' => $loginAttemptCheck['message'],
            ])->withInput($request->only('email'));
        }

        // 1-4: 로그인 시도
        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $user = Auth::user();

            // 로그인 성공 시 시도 횟수 초기화
            $this->resetLoginAttempts($credentials['email']);
            $this->resetPasswordErrors($credentials['email']);

            // 1-4.1: 승인 상태 검사
            $approvalCheck = $this->checkUserApproval($user);
            if (!$approvalCheck['success']) {
                return redirect()->route('login.approval')
                    ->with('error', $approvalCheck['message']);
            }

            // 1-5: 성공 시 처리
            $user->last_login_at = now();
            $user->login_count = ($user->login_count ?? 0) + 1;
            $user->save();

            if ($request->hasSession()) {
                $request->session()->regenerate();
            }

            // /home 라우트가 존재하는지 확인하고, 없으면 /로 리다이렉션
            $redirectUrl = $this->getRedirectUrl();
            return redirect()->intended($redirectUrl);
        }

        // 1-6: 실패 시 처리
        // 로그인 실패 시 시도 횟수 증가 및 비밀번호 오류 기록
        $this->incrementLoginAttempts($credentials['email']);
        $this->recordPasswordError($credentials['email'], $request);

        return back()->withErrors([
            'email' => '이메일 또는 비밀번호가 일치하지 않습니다.',
        ])->withInput($request->only('email'));
    }

    /**
     * AJAX 로그인 처리 (store() 메서드에서 호출)
     *
     * 호출 구조:
     * 2. loginAjax() (현재 메소드)
     *    ├── 2-1. 로그인 기능 활성화 확인
     *    ├── 2-2. 입력 데이터 검증
     *    │   ├── 2-2.1. 이메일 검증 (required, email)
     *    │   └── 2-2.2. 비밀번호 검증 (required)
     *    ├── 2-3. 1단계: 일반 회원 로그인 시도
     *    │   ├── 2-3.1. Auth::attempt() 호출
     *    │   ├── 2-3.2. 성공 시 처리
     *    │   │   ├── 2-3.2.1. 사용자 정보 업데이트
     *    │   │   │   ├── 2-3.2.1.1. last_login_at 설정
     *    │   │   │   └── 2-3.2.1.2. login_count 증가
     *    │   │   ├── 2-3.2.2. 세션 재생성
     *    │   │   ├── 2-3.2.3. 2FA 확인 (주석 처리됨)
     *    │   │   └── 2-3.2.4. 성공 응답 반환
     *    │   └── 2-3.3. 실패 시 2단계로 진행
     *    ├── 2-4. 2단계: 예약된 회원 검사
     *    │   ├── 2-4.1. 예약 승인 요청 조회
     *    │   ├── 2-4.2. 상태별 처리
     *    │   │   ├── 2-4.2.1. 대기 상태: 승인 대기 안내
     *    │   │   ├── 2-4.2.2. 거절 상태: 거절 안내
     *    │   │   └── 2-4.2.3. 승인 상태: 일반 로그인 실패로 처리
     *    │   └── 2-4.3. 예약 정보 없음: 일반 로그인 실패로 처리
     *    └── 2-5. 최종 실패 응답
     */
    public function loginAjax(Request $request)
    {
        // 2-1: 로그인 기능 활성화 확인
        if (!$this->isLoginEnabled()) {
            return response()->json([
                'success' => false,
                'message' => '로그인 기능이 비활성화되었습니다.'
            ], 403);
        }

        // 2-2: 입력 데이터 검증
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $ip = $request->ip();
        $ua = $request->header('User-Agent');
        $logStatus = 'fail';
        $logMsg = null;

        // 2-3: 1단계 - 일반 회원 로그인 시도

        // 2-3.0: 로그인 시도 제한 확인 (새로운 비밀번호 오류 제한 기능)
        $loginAttemptCheck = $this->checkLoginAttemptsWithPasswordErrors($credentials['email'], $request);
        if (!$loginAttemptCheck['allowed']) {
            return response()->json([
                'success' => false,
                'message' => $loginAttemptCheck['message'],
                'error_code' => $loginAttemptCheck['error_code'] ?? 'ACCOUNT_LOCKED'
            ], 423);
        }

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $user = Auth::user();

            // 로그인 성공 시 시도 횟수 초기화
            $this->resetLoginAttempts($credentials['email']);
            $this->resetPasswordErrors($credentials['email']);

            // 2-3.1: 승인 상태 검사
            $approvalCheck = $this->checkUserApproval($user);
            if (!$approvalCheck['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $approvalCheck['message'],
                    'error_code' => $approvalCheck['error_code'],
                    'redirect' => $approvalCheck['redirect'],
                    'approval_required' => $approvalCheck['approval_required'] ?? false,
                    'type' => 'error'
                ], 403, [], JSON_UNESCAPED_UNICODE);
            }

            // 2-3.2: 성공 시 처리
            // 2-3.2.1: 사용자 정보 업데이트
            $user->last_login_at = now();
            $user->login_count = ($user->login_count ?? 0) + 1;
            $user->save();

            $logStatus = 'success';
            $logMsg = '로그인 성공';

            // 2-3.2.2: 세션 재생성
            if ($request->hasSession()) {
                $request->session()->regenerate();
            }

            // 2-3.2.3: 2FA 확인 (현재 주석 처리됨)
            // if ($user->has2FAEnabled()) {
            //     return response()->json([
            //         'success' => true,
            //         'message' => '2FA 인증이 필요합니다.',
            //         'redirect' => route('user.2fa.challenge'),
            //         'user' => $admin
            //     ]);
            // }

            // 2-3.2.4: 성공 응답 반환
            $redirectUrl = $this->getRedirectUrl();
            return response()->json([
                'success' => true,
                'message' => '로그인 성공',
                'redirect' => $redirectUrl,
                'user' => $user
            ]);
        }

        // 로그인 실패 시 시도 횟수 증가 및 비밀번호 오류 기록
        $this->incrementLoginAttempts($credentials['email']);
        $this->recordPasswordError($credentials['email'], $request);

        // 2-4: 2단계 - 예약된 회원 검사
        $email = $credentials['email'];
        $reservedApproval = \Jiny\Auth\App\Models\UserReservedApproval::where('email', $email)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($reservedApproval) {
            // 2-4.2: 상태별 처리
            switch ($reservedApproval->status) {
                case 'pending':
                    // 2-4.2.1: 대기 상태 - 승인 대기 안내
                    return response()->json([
                        'success' => false,
                        'message' => '회원가입 신청이 승인 대기 중입니다. 관리자 승인 후 로그인할 수 있습니다.',
                        'redirect' => route('login.reserved'),
                        'status' => 'pending',
                        'approval_id' => $reservedApproval->id
                    ], 403);

                case 'rejected':
                    // 2-4.2.2: 거절 상태 - 거절 안내
                    return response()->json([
                        'success' => false,
                        'message' => '회원가입 신청이 거절되었습니다. 다른 이메일로 다시 신청해주세요.',
                        'redirect' => route('login.reserved'),
                        'status' => 'rejected',
                        'approval_id' => $reservedApproval->id
                    ], 403);

                case 'approved':
                    // 2-4.2.3: 승인 상태 - 일반 로그인 실패로 처리
                    $logMsg = '이메일 또는 비밀번호가 일치하지 않습니다.';
                    break;

                default:
                    // 알 수 없는 상태 - 일반 로그인 실패로 처리
                    $logMsg = '이메일 또는 비밀번호가 일치하지 않습니다.';
                    break;
            }
        } else {
            // 2-4.3: 예약 정보 없음 - 일반 로그인 실패로 처리
            $logMsg = '이메일 또는 비밀번호가 일치하지 않습니다.';
        }

        // 2-5: 최종 실패 응답
        return response()->json([
            'success' => false,
            'message' => $logMsg,
            'error_code' => 'LOGIN_FAILED'
        ], 401);
    }

    /**
     * 사용자 승인 상태 검사
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable $user
     * @return array
     */
    private function checkUserApproval($user): array
    {
        $config = config('admin.auth', []);
        $approvalRequired = $config['auth']['registration']['approval_required'] ?? false;

        // 승인이 필요하지 않은 경우 통과
        if (!$approvalRequired) {
            return ['success' => true];
        }

        // 이미 승인된 사용자는 통과
        if ($user->is_approved) {
            return ['success' => true];
        }

        // 승인되지 않은 사용자는 승인 페이지로 리다이렉션
        Log::info('승인되지 않은 사용자 로그인 시도', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);

        return [
            'success' => false,
            'message' => '관리자 승인이 필요합니다. 승인 후 로그인할 수 있습니다.',
            'error_code' => 'APPROVAL_REQUIRED',
            'redirect' => route('login.approval'),
            'approval_required' => true
        ];
    }

    /**
     * 리다이렉트 URL 결정
     * /home 라우트가 존재하면 /home, 없으면 /로 리다이렉션
     */
    private function getRedirectUrl()
    {
        // /home 라우트가 존재하는지 확인
        try {
            $routes = Route::getRoutes();
            $homeRoute = $routes->getByName('home');

            if ($homeRoute) {
                return '/home';
            }
        } catch (\Exception $e) {
            // route('home')이 존재하지 않는 경우
        }

        // /home 라우트가 없거나 예외가 발생한 경우 /로 리다이렉션
        return '/';
    }

    /**
     * 로그인 기능이 활성화되어 있는지 확인합니다.
     *
     * @return bool
     */
    private function isLoginEnabled(): bool
    {
        $config = config('admin.auth', []);

        // login.enabled 확인
        $enabled = $config['login']['enabled'] ?? true;

        // 문자열로 저장된 경우 boolean으로 변환
        if (is_string($enabled)) {
            $enabled = filter_var($enabled, FILTER_VALIDATE_BOOLEAN);
        }

        // 디버깅을 위한 로그
        Log::info('LoginSessionController - Login enabled status:', [
            'enabled' => $enabled,
            'login_enabled' => $config['login']['enabled'] ?? 'not_set'
        ]);

        return $enabled;
    }

    /**
     * 로그인 시도 제한 확인 (기존 메서드 - 호환성을 위해 유지)
     */
    private function checkLoginAttempts($email)
    {
        $maxAttempts = config('admin.auth.login.max_attempts', 5);
        $lockoutTime = config('admin.auth.login.lockout_time', 600);

        $cacheKey = 'login_attempts_' . md5($email);
        $attempts = Cache::get($cacheKey, 0);

        if ($attempts >= $maxAttempts) {
            return false; // 계정 잠금
        }

        return true; // 로그인 시도 가능
    }

    /**
     * 로그인 시도 제한 확인 (새로운 비밀번호 오류 제한 기능)
     */
    private function checkLoginAttemptsWithPasswordErrors($email, Request $request): array
    {
        $maxAttempts = config('admin.auth.login.max_attempts', 5);
        $permanentLockoutAttempts = config('admin.auth.login.permanent_lockout_attempts', 25);
        $lockoutTime = config('admin.auth.login.lockout_time', 15);

        // 1. 기존 캐시 기반 제한 확인
        $cacheKey = 'login_attempts_' . md5($email);
        $attempts = Cache::get($cacheKey, 0);

        if ($attempts >= $maxAttempts) {
            return [
                'allowed' => false,
                'message' => '계정이 잠금되었습니다. 잠시 후 다시 시도해주세요.',
                'error_code' => 'ACCOUNT_LOCKED_TEMPORARY'
            ];
        }

        // 2. 비밀번호 오류 기반 제한 확인
        $passwordErrors = UserPasswordError::getRecentErrorsByEmail($email, 24);

        if ($passwordErrors->isNotEmpty()) {
            $latestError = $passwordErrors->first();
            $consecutiveErrors = $latestError->consecutive_errors;

            // 영구 잠금 확인
            if ($consecutiveErrors >= $permanentLockoutAttempts) {
                return [
                    'allowed' => false,
                    'message' => '계정이 영구 잠금되었습니다. 관리자에게 문의하세요.',
                    'error_code' => 'ACCOUNT_LOCKED_PERMANENT'
                ];
            }

            // 임시 잠금 확인
            if ($consecutiveErrors >= $maxAttempts) {
                // 잠금 시간 확인
                if ($latestError->locked_at && !$latestError->isLockoutExpired()) {
                    $remainingMinutes = $latestError->locked_at->addMinutes($lockoutTime)->diffInMinutes(now());
                    return [
                        'allowed' => false,
                        'message' => "계정이 잠금되었습니다. {$remainingMinutes}분 후에 다시 시도해주세요.",
                        'error_code' => 'ACCOUNT_LOCKED_TEMPORARY'
                    ];
                }
            }
        }

        return [
            'allowed' => true,
            'message' => ''
        ];
    }

    /**
     * 로그인 시도 횟수 증가
     */
    private function incrementLoginAttempts($email)
    {
        $cacheKey = 'login_attempts_' . md5($email);
        $attempts = Cache::get($cacheKey, 0);
        $lockoutTime = config('admin.auth.login.lockout_time', 600);

        Cache::put($cacheKey, $attempts + 1, $lockoutTime);
    }

    /**
     * 로그인 시도 횟수 초기화
     */
    private function resetLoginAttempts($email)
    {
        $cacheKey = 'login_attempts_' . md5($email);
        Cache::forget($cacheKey);
    }

    /**
     * 비밀번호 오류 기록
     */
    private function recordPasswordError($email, Request $request): void
    {
        try {
            $user = Account::where('email', $email)->first();

            $data = [
                'user_id' => $user ? $user->id : null,
                'email' => $email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'error_type' => UserPasswordError::ERROR_TYPE_WRONG_PASSWORD,
                'error_message' => '잘못된 비밀번호',
                'metadata' => [
                    'user_agent' => $request->userAgent(),
                    'referer' => $request->header('referer'),
                    'request_id' => uniqid()
                ]
            ];

            $passwordError = UserPasswordError::recordError($data);

            // 연속 오류 횟수에 따른 계정 잠금 처리
            $maxAttempts = config('admin.auth.login.max_attempts', 5);
            $permanentLockoutAttempts = config('admin.auth.login.permanent_lockout_attempts', 25);

            if ($passwordError->consecutive_errors >= $permanentLockoutAttempts) {
                // 영구 잠금
                $passwordError->lockAccount('연속 로그인 실패로 인한 영구 잠금');
                Log::warning('계정 영구 잠금', [
                    'email' => $email,
                    'consecutive_errors' => $passwordError->consecutive_errors,
                    'ip' => $request->ip()
                ]);
            } elseif ($passwordError->consecutive_errors >= $maxAttempts) {
                // 임시 잠금
                $passwordError->lockAccount('연속 로그인 실패로 인한 임시 잠금');
                Log::info('계정 임시 잠금', [
                    'email' => $email,
                    'consecutive_errors' => $passwordError->consecutive_errors,
                    'ip' => $request->ip()
                ]);
            }
        } catch (\Exception $e) {
            Log::error('비밀번호 오류 기록 실패', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * 비밀번호 오류 초기화
     */
    private function resetPasswordErrors($email): void
    {
        try {
            // 최근 24시간 내의 성공적인 로그인 이후의 오류 기록들을 초기화
            UserPasswordError::where('email', $email)
                ->where('created_at', '>=', Carbon::now()->subHours(24))
                ->update([
                    'status' => UserPasswordError::STATUS_UNLOCKED,
                    'unlocked_at' => Carbon::now()
                ]);
        } catch (\Exception $e) {
            Log::error('비밀번호 오류 초기화 실패', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);
        }
    }
}
