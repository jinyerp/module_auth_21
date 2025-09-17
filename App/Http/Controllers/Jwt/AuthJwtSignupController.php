<?php

namespace Jiny\Auth\App\Http\Controllers\Jwt;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Jiny\Auth\App\Models\Account;
use Tymon\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;

/**
 * JWT 회원가입 컨트롤러
 * JWT 토큰 기반 회원가입 처리
 */
class AuthJwtSignupController extends Controller
{
    private $config;
    private $viewFile;

    public function __construct()
    {
        $this->config = config('jiny-auth');
        $this->viewFile = 'jiny-auth::jwt.signup';
    }

    /**
     * JWT 회원가입 폼 표시
     */
    public function index()
    {
        // 회원가입 기능 활성화 확인
        if (!$this->isRegistrationEnabled()) {
            return view('jiny-auth::auth.regist_disabled');
        }

        return view($this->viewFile, [
            'password_rules' => $this->getPasswordRules(),
            'terms_enabled' => $this->config['registration']['terms_required'] ?? false
        ]);
    }

    /**
     * JWT 회원가입 처리
     */
    public function signup(Request $request)
    {
        // 회원가입 기능 활성화 확인
        if (!$this->isRegistrationEnabled()) {
            return response()->json([
                'success' => false,
                'message' => '회원가입이 비활성화되어 있습니다.'
            ], 403);
        }

        // 유효성 검사
        $validator = $this->validateSignup($request);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            // 계정 생성
            $account = $this->createAccount($request);

            // JWT 토큰 생성
            $token = JWTAuth::fromUser($account);
            $refreshToken = $this->generateRefreshToken($account);

            // 토큰 정보 저장
            $this->saveTokenInfo($account->id, $token, $refreshToken);

            // 회원가입 로그 기록
            $this->logSignup($account);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '회원가입이 완료되었습니다.',
                'data' => [
                    'access_token' => $token,
                    'refresh_token' => $refreshToken,
                    'token_type' => 'bearer',
                    'expires_in' => config('jwt.ttl') * 60,
                    'user' => [
                        'id' => $account->id,
                        'name' => $account->name,
                        'email' => $account->email
                    ]
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('JWT Signup failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => '회원가입 중 오류가 발생했습니다.'
            ], 500);
        }
    }

    /**
     * 회원가입 유효성 검사
     */
    protected function validateSignup(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:accounts',
            'password' => $this->getPasswordValidationRules(),
            'password_confirmation' => 'required|same:password',
        ];

        // 전화번호 필수 여부
        if ($this->config['registration']['phone_required'] ?? false) {
            $rules['phone'] = 'required|string|regex:/^[0-9-+()]+$/';
        }

        // 약관 동의 필수 여부
        if ($this->config['registration']['terms_required'] ?? false) {
            $rules['terms_agreed'] = 'required|accepted';
        }

        return Validator::make($request->all(), $rules);
    }

    /**
     * 계정 생성
     */
    protected function createAccount(Request $request)
    {
        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'status' => $this->getInitialStatus(),
            'email_verified_at' => $this->shouldAutoVerifyEmail() ? now() : null,
            'created_at' => now(),
            'updated_at' => now()
        ];

        return Account::create($data);
    }

    /**
     * Refresh Token 생성
     */
    protected function generateRefreshToken($account)
    {
        return Hash::make($account->id . '|' . now()->timestamp . '|' . str()->random(40));
    }

    /**
     * 토큰 정보 저장
     */
    protected function saveTokenInfo($accountId, $accessToken, $refreshToken)
    {
        DB::table('jwt_tokens')->insert([
            'account_id' => $accountId,
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'expires_at' => Carbon::now()->addMinutes(config('jwt.ttl')),
            'refresh_expires_at' => Carbon::now()->addDays(30),
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * 회원가입 로그 기록
     */
    protected function logSignup($account)
    {
        DB::table('account_logs')->insert([
            'account_id' => $account->id,
            'action' => 'signup',
            'description' => 'JWT 회원가입',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now()
        ]);
    }

    /**
     * 회원가입 활성화 확인
     */
    protected function isRegistrationEnabled()
    {
        return $this->config['registration']['enabled'] ?? true;
    }

    /**
     * 패스워드 규칙 가져오기
     */
    protected function getPasswordRules()
    {
        return $this->config['password']['rules'] ?? [];
    }

    /**
     * 패스워드 유효성 검사 규칙
     */
    protected function getPasswordValidationRules()
    {
        $rules = ['required', 'string', 'min:8'];
        
        $passwordRules = $this->getPasswordRules();
        
        if ($passwordRules['require_uppercase'] ?? false) {
            $rules[] = 'regex:/[A-Z]/';
        }
        
        if ($passwordRules['require_lowercase'] ?? false) {
            $rules[] = 'regex:/[a-z]/';
        }
        
        if ($passwordRules['require_numbers'] ?? false) {
            $rules[] = 'regex:/[0-9]/';
        }
        
        if ($passwordRules['require_special'] ?? false) {
            $rules[] = 'regex:/[@$!%*?&#]/';
        }

        return $rules;
    }

    /**
     * 초기 계정 상태 가져오기
     */
    protected function getInitialStatus()
    {
        if ($this->config['registration']['requires_approval'] ?? false) {
            return 'pending';
        }
        
        if ($this->config['registration']['requires_email_verification'] ?? false) {
            return 'unverified';
        }

        return 'active';
    }

    /**
     * 이메일 자동 인증 여부
     */
    protected function shouldAutoVerifyEmail()
    {
        return !($this->config['registration']['requires_email_verification'] ?? false);
    }
}