<?php

namespace Jiny\Auth\App\Http\Controllers\Jwt;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use Tymon\JWTAuthJwtLoginConarbon\Carbon;
use Tymon\JWTAuth\Facades\JWTAuth;

use Jiny\Auth\App\Models\User;
use Jiny\Auth\App\Models\JwtToken;
use Jiny\Auth\App\Models\UserLog;
use Jiny\Auth\App\Models\UserLogStatus;


class AuthJwtSigninController extends Controller
{
    protected $config;
    protected $settings;

    public function __construct()
    {
        $this->config = config('jiny-auth.settings', []);
        $this->settings = $this->config ?: [];

        // 디버그: 설정 로드 상태 확인
        Log::info('AuthJwtSigninController 설정 로드', [
            'config_exists' => !empty($this->config),
            'settings_exists' => !empty($this->settings),
            'auth_exists' => isset($this->settings['auth']),
            'settings_keys' => array_keys($this->settings)
        ]);
    }

    public function index(Request $request)
    {
        $token = trim($this->getTokenFromRequest($request));
        Log::info('쿠키에서 추출한 토큰', ['token' => $token]);
        if ($token) {
            $payload = $this->verifyToken($token);
            if ($payload) {
                return redirect()->route('home');
            }
        }
        $registerEnabled = ($this->settings['auth']['register_enable'] ?? true);
        $data = [
            'register_enabled' => $registerEnabled
        ];
        if (!($this->settings['auth']['login_enable'] ?? true)) {
            $viewFile = ($this->settings['views']['login']['disabled'] ?? 'auth.login.disabled');
            return view($viewFile);
        }
        if ($this->isLoginBlocked()) {
            $viewFile = ($this->settings['views']['login']['disabled'] ?? 'auth.login.disabled');
            return view($viewFile, [
                'message' => '로그인이 일시적으로 차단되었습니다. 잠시 후 다시 시도해주세요.'
            ]);
        }
        $viewFile = 'jiny-auth::jwt.signin';
        return view($viewFile, $data);
    }

    public function store(Request $request)
    {

        if (!($this->settings['auth']['login_enable'] ?? true)) {
            return response()->json([
                'success' => false,
                'message' => '로그인 기능이 비활성화되었습니다.'
            ], 403);
        }
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:' . ($this->settings['auth']['password_min_length'] ?? 8),
        ], [
            'email.required' => '이메일을 입력해주세요.',
            'email.email' => '올바른 이메일 형식을 입력해주세요.',
            'password.required' => '비밀번호를 입력해주세요.',
            'password.min' => '비밀번호는 최소 ' . ($this->settings['auth']['password_min_length'] ?? 8) . '자 이상이어야 합니다.',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => '입력 정보가 올바르지 않습니다.',
                'errors' => $validator->errors()
            ], 422);
        }
        $email = $request->email;
        $password = $request->password;
        $user = User::where('email', $email)->first();
        if (!$user || !Hash::check($password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => '이메일 또는 비밀번호가 올바르지 않습니다.'
            ], 401);
        }
        $token = JWTAuth::fromUser($user);
        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => '토큰 발급에 실패했습니다.'
            ], 500);
        }
        // JWT 토큰 DB 저장
        JwtToken::create([
            'user_id'    => $user->id,
            'token'      => $token,
            'is_revoked' => false,
            'created_at' => now(),
            'expires_at' => now()->addSeconds(auth('api')->factory()->getTTL() * 60),
        ]);
        // 사용자 로그 기록
        UserLog::createLog($user->id, 'jwt', 'login');
        UserLogStatus::setStatus($user->id, 'jwt_login', now());
        $response = response()->json([
            'success' => true,
            'message' => '로그인 성공',
            'data' => [
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth('api')->factory()->getTTL() * 60,
            ]
        ]);
        $response->cookie('jwt_token', $token, 60 * 24 * 7, '/', null, false, true, false, 'Lax');
        return $response;
    }

    /**
     * JWT 회원가입 처리
     */
    public function register(Request $request)
    {
        // 회원가입 기능이 비활성화된 경우
        if (!($this->settings['auth']['register_enable'] ?? true)) {
            return response()->json([
                'success' => false,
                'message' => '회원가입 기능이 비활성화되었습니다.'
            ], 403);
        }

        // 입력 검증
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:' . ($this->settings['auth']['password_min_length'] ?? 8) . '|confirmed',
            'password_confirmation' => 'required|string',
        ], [
            'name.required' => '이름을 입력해주세요.',
            'name.max' => '이름은 255자 이하여야 합니다.',
            'email.required' => '이메일을 입력해주세요.',
            'email.email' => '올바른 이메일 형식을 입력해주세요.',
            'email.max' => '이메일은 255자 이하여야 합니다.',
            'password.required' => '비밀번호를 입력해주세요.',
            'password.min' => '비밀번호는 최소 ' . ($this->settings['auth']['password_min_length'] ?? 8) . '자 이상이어야 합니다.',
            'password.confirmed' => '비밀번호 확인이 일치하지 않습니다.',
            'password_confirmation.required' => '비밀번호 확인을 입력해주세요.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => '입력 정보가 올바르지 않습니다.',
                'errors' => $validator->errors()
            ], 422);
        }

        // JWT 회원가입 처리
        $result = $this->jwtAuthService->register($request->all());

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => '회원가입 성공',
                'data' => $result['data']
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => $result['message']
            ], 400);
        }
    }

    /**
     * JWT 토큰 갱신
     */
    public function refresh(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'refresh_token' => 'required|string',
        ], [
            'refresh_token.required' => '리프레시 토큰이 필요합니다.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => '입력 정보가 올바르지 않습니다.',
                'errors' => $validator->errors()
            ], 422);
        }

        $refreshToken = $request->refresh_token;
        $result = $this->jwtAuthService->refreshToken($refreshToken);

        if ($result) {
            return response()->json([
                'success' => true,
                'message' => '토큰 갱신 성공',
                'data' => $result
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => '토큰 갱신에 실패했습니다.'
            ], 401);
        }
    }

    /**
     * JWT 로그아웃
     */
    // public function logout(Request $request)
    // {
    //     // GET 요청인 경우 (브라우저에서 직접 접근)
    //     if ($request->isMethod('get')) {
    //         // Authorization 헤더에서 토큰 추출
    //         $token = $this->getTokenFromRequest($request);

    //         if (!$token) {
    //             // 토큰이 없으면 로그인 페이지로 리다이렉트
    //             return redirect()->route('jwt.login')
    //                 ->with('error', '로그인 상태가 아닙니다.');
    //         }

    //                     $result = $this->jwtAuthService->revokeToken($token);

    //         if ($result) {
    //             // 쿠키 삭제
    //             $response = redirect()->route('jwt.login')
    //                 ->with('success', '로그아웃되었습니다.');

    //             $response->cookie('jwt_token', '', -1, '/');
    //             $response->cookie('jwt_refresh_token', '', -1, '/');

    //             return $response;
    //         } else {
    //             return redirect()->route('jwt.login')
    //                 ->with('error', '로그아웃 처리에 실패했습니다.');
    //         }
    //     }

    //     // POST 요청인 경우 (API 호출)
    //     $validator = Validator::make($request->all(), [
    //         'access_token' => 'required|string',
    //     ], [
    //         'access_token.required' => '액세스 토큰이 필요합니다.',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => '입력 정보가 올바르지 않습니다.',
    //             'errors' => $validator->errors()
    //         ], 422);
    //     }

    //     $accessToken = $request->access_token;
    //     $result = $this->jwtAuthService->revokeToken($accessToken);

    //     if ($result) {
    //         return response()->json([
    //             'success' => true,
    //             'message' => '로그아웃되었습니다.'
    //         ]);
    //     } else {
    //         return response()->json([
    //             'success' => false,
    //             'message' => '로그아웃 처리에 실패했습니다.'
    //         ], 400);
    //     }
    // }

    /**
     * JWT 로그아웃 처리 (/signout)
     */
    public function signout(Request $request)
    {
        $token = $this->getTokenFromRequest($request);
        $userId = null;
        if ($token) {
            try {
                $payload = \Tymon\JWTAuth\Facades\JWTAuth::setToken($token)->getPayload();
                $userId = $payload['sub'] ?? null;
                JWTAuth::setToken($token)->invalidate();
            } catch (\Exception $e) {}
            // DB 토큰 폐기 처리
            JwtToken::where('token', $token)->update([
                'is_revoked' => true,
                'revoked_at' => now()
            ]);
        }
        // 사용자 로그 기록 (로그아웃)
        if ($userId) {
            UserLog::createLog($userId, 'jwt', 'logout');
            UserLogStatus::setStatus($userId, 'jwt_logout', now());
        }
        // 쿠키 삭제 및 /signin으로 리다이렉트
        $response = redirect('/signin')
            ->with('success', '로그아웃되었습니다.');
        $response->cookie('jwt_token', '', -1, '/');
        $response->cookie('jwt_refresh_token', '', -1, '/');
        return $response;
    }

    protected function verify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'access_token' => 'required|string',
        ], [
            'access_token.required' => '액세스 토큰이 필요합니다.',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => '입력 정보가 올바르지 않습니다.',
                'errors' => $validator->errors()
            ], 422);
        }
        $accessToken = $request->access_token;
        $payload = $this->verifyToken($accessToken);
        if ($payload) {
            return response()->json([
                'success' => true,
                'message' => '토큰이 유효합니다.',
                'data' => [
                    'user_id' => $payload['sub'],
                    'email' => $payload['email'],
                    'expires_at' => $payload['exp']
                ]
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => '토큰이 유효하지 않습니다.'
            ], 401);
        }
    }

    /**
     * JWT 토큰 유효성 검증 (tymon/jwt-auth)
     */
    public function verifyToken($token)
    {
        try {
            $payload = \Tymon\JWTAuth\Facades\JWTAuth::setToken($token)->getPayload();
            return $payload->toArray();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 로그인 차단 확인
     */
    protected function isLoginBlocked()
    {
        $ip = request()->ip();
        $blockKey = "jwt_login_blocked_{$ip}";
        return Cache::has($blockKey);
    }

    /**
     * 로그인 시도 횟수 증가
     */
    protected function incrementLoginAttempts($email)
    {
        $key = "jwt_login_attempts_{$email}";
        $attempts = Cache::get($key, 0);
        $maxAttempts = ($this->settings['auth']['max_login_attempts'] ?? 5);

        if ($attempts >= $maxAttempts) {
            $lockoutTime = ($this->settings['auth']['lockout_time'] ?? 600);
            Cache::put($key, $attempts + 1, $lockoutTime);

            // IP 기반 차단
            $ip = request()->ip();
            $blockKey = "jwt_login_blocked_{$ip}";
            Cache::put($blockKey, true, $lockoutTime);
        } else {
            Cache::put($key, $attempts + 1, 300); // 5분간 유지
        }
    }

    /**
     * 로그인 시도 횟수 초기화
     */
    protected function clearLoginAttempts($email)
    {
        $key = "jwt_login_attempts_{$email}";
        Cache::forget($key);
    }

    /**
     * 로그인 실패 로그
     */
    protected function logFailedLogin($email, $reason)
    {
        if ($this->settings['auth']['login_log_enabled'] ?? true) {
            Log::warning('JWT 로그인 실패', [
                'email' => $email,
                'reason' => $reason,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }
    }

    /**
     * 요청에서 JWT 토큰을 추출합니다.
     */
    protected function getTokenFromRequest(Request $request): ?string
    {
        // 쿠키에서 토큰 추출 (우선순위 높음)
        $token = $request->cookie('jwt_token');
        if ($token) {
            return $token;
        }

        // Authorization 헤더에서 토큰 추출
        $authorization = $request->header('Authorization');
        if ($authorization && preg_match('/Bearer\s+(.*)$/i', $authorization, $matches)) {
            return $matches[1];
        }

        // URL 파라미터에서 토큰 추출
        $token = $request->query('token');
        if ($token) {
            return $token;
        }

        return null;
    }
}
