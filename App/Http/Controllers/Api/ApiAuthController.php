<?php

namespace Jiny\Auth\App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Validation\ValidationException;

/**
 * API 인증 컨트롤러
 * Sanctum 기반 API 토큰 인증
 */
class ApiAuthController extends Controller
{
    /**
     * API 로그인
     * POST /api/auth/login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required|string|max:255',
        ]);

        $user = User::where('email', $request->email)->first();

        // 계정 확인
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['인증 정보가 일치하지 않습니다.'],
            ]);
        }

        // 계정 상태 확인
        if ($user->status !== 'active') {
            throw ValidationException::withMessages([
                'email' => ['계정이 비활성화 상태입니다.'],
            ]);
        }

        // 기존 토큰 삭제 (선택적)
        if ($request->has('revoke_existing') && $request->revoke_existing) {
            $user->tokens()->where('name', $request->device_name)->delete();
        }

        // 새 토큰 생성
        $token = $user->createToken($request->device_name, ['*'])->plainTextToken;

        // 로그인 이력 기록
        DB::table('login_histories')->insert([
            'user_id' => $user->id,
            'login_at' => now(),
            'login_type' => 'api',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'device_name' => $request->device_name,
            'created_at' => now()
        ]);

        // 활동 로그 기록
        DB::table('user_logs')->insert([
            'user_id' => $user->id,
            'action' => 'api_login',
            'description' => 'API 로그인: ' . $request->device_name,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => '로그인 성공',
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
            ]
        ]);
    }

    /**
     * 인증된 사용자 정보 조회
     * GET /api/user
     */
    public function user(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'status' => $user->status,
                'email_verified_at' => $user->email_verified_at,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ]
        ]);
    }

    /**
     * API 로그아웃
     * POST /api/auth/logout
     */
    public function logout(Request $request)
    {
        $user = $request->user();
        
        // 현재 토큰 삭제
        $request->user()->currentAccessToken()->delete();

        // 로그아웃 기록
        DB::table('login_histories')
            ->where('user_id', $user->id)
            ->whereNull('logout_at')
            ->orderBy('login_at', 'desc')
            ->limit(1)
            ->update([
                'logout_at' => now(),
                'logout_type' => 'api',
                'updated_at' => now()
            ]);

        // 활동 로그 기록
        DB::table('user_logs')->insert([
            'user_id' => $user->id,
            'action' => 'api_logout',
            'description' => 'API 로그아웃',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => '로그아웃 성공'
        ]);
    }

    /**
     * 모든 토큰 무효화
     * POST /api/auth/logout-all
     */
    public function logoutAll(Request $request)
    {
        $user = $request->user();
        
        // 모든 토큰 삭제
        $user->tokens()->delete();

        // 활동 로그 기록
        DB::table('user_logs')->insert([
            'user_id' => $user->id,
            'action' => 'api_logout_all',
            'description' => '모든 API 토큰 무효화',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => '모든 토큰이 무효화되었습니다.'
        ]);
    }

    /**
     * 토큰 갱신
     * POST /api/auth/refresh
     */
    public function refresh(Request $request)
    {
        $request->validate([
            'device_name' => 'required|string|max:255',
        ]);

        $user = $request->user();
        
        // 현재 토큰 삭제
        $request->user()->currentAccessToken()->delete();
        
        // 새 토큰 생성
        $token = $user->createToken($request->device_name, ['*'])->plainTextToken;

        // 활동 로그 기록
        DB::table('user_logs')->insert([
            'user_id' => $user->id,
            'action' => 'api_token_refresh',
            'description' => 'API 토큰 갱신: ' . $request->device_name,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => '토큰 갱신 성공',
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
            ]
        ]);
    }

    /**
     * 토큰 목록 조회
     * GET /api/auth/tokens
     */
    public function tokens(Request $request)
    {
        $user = $request->user();
        
        $tokens = $user->tokens()->select('id', 'name', 'last_used_at', 'created_at')->get();

        return response()->json([
            'success' => true,
            'data' => $tokens
        ]);
    }

    /**
     * 특정 토큰 삭제
     * DELETE /api/auth/tokens/{id}
     */
    public function revokeToken(Request $request, $id)
    {
        $user = $request->user();
        
        $token = $user->tokens()->find($id);
        
        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => '토큰을 찾을 수 없습니다.'
            ], 404);
        }

        $token->delete();

        // 활동 로그 기록
        DB::table('user_logs')->insert([
            'user_id' => $user->id,
            'action' => 'api_token_revoke',
            'description' => 'API 토큰 삭제: ' . $token->name,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => '토큰이 삭제되었습니다.'
        ]);
    }

    /**
     * 회원가입
     * POST /api/auth/register
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'device_name' => 'required|string|max:255',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'status' => 'active',
            'email_verified_at' => now(), // API 회원가입은 자동 인증
        ]);

        // 토큰 생성
        $token = $user->createToken($validated['device_name'], ['*'])->plainTextToken;

        // 활동 로그 기록
        DB::table('user_logs')->insert([
            'user_id' => $user->id,
            'action' => 'api_register',
            'description' => 'API 회원가입',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => '회원가입 성공',
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
            ]
        ], 201);
    }
}