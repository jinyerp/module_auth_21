<?php

namespace Jiny\Auth\App\Http\Controllers\Jwt;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

/**
 * JWT 로그아웃 컨트롤러
 * JWT 토큰 무효화 처리
 */
class AuthJwtSignoutController extends Controller
{
    /**
     * JWT 로그아웃 폼 표시 (GET)
     */
    public function index()
    {
        return view('jiny-auth::jwt.signout');
    }

    /**
     * JWT 로그아웃 처리 (POST)
     */
    public function signout(Request $request)
    {
        try {
            // 현재 토큰 가져오기
            $token = JWTAuth::getToken();
            
            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => '유효한 토큰이 없습니다.'
                ], 401);
            }

            // 토큰 페이로드 가져오기 (로그 기록용)
            $payload = JWTAuth::getPayload($token);
            $accountId = $payload->get('sub');

            // 토큰 무효화 (블랙리스트 추가)
            JWTAuth::invalidate($token);

            // DB에서 토큰 정보 삭제 또는 무효화
            $this->invalidateStoredToken($token->get());

            // 로그아웃 로그 기록
            $this->logSignout($accountId);

            return response()->json([
                'success' => true,
                'message' => '로그아웃되었습니다.'
            ]);

        } catch (JWTException $e) {
            Log::error('JWT Signout failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => '로그아웃 처리 중 오류가 발생했습니다.'
            ], 500);
        }
    }

    /**
     * 모든 토큰 무효화 (다른 기기에서도 로그아웃)
     */
    public function signoutAll(Request $request)
    {
        try {
            $token = JWTAuth::getToken();
            
            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => '유효한 토큰이 없습니다.'
                ], 401);
            }

            $payload = JWTAuth::getPayload($token);
            $accountId = $payload->get('sub');

            // 해당 사용자의 모든 토큰 무효화
            $this->invalidateAllUserTokens($accountId);

            // 현재 토큰도 블랙리스트에 추가
            JWTAuth::invalidate($token);

            // 로그 기록
            $this->logSignoutAll($accountId);

            return response()->json([
                'success' => true,
                'message' => '모든 기기에서 로그아웃되었습니다.'
            ]);

        } catch (JWTException $e) {
            Log::error('JWT Signout All failed', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => '로그아웃 처리 중 오류가 발생했습니다.'
            ], 500);
        }
    }

    /**
     * 저장된 토큰 무효화
     */
    protected function invalidateStoredToken($tokenString)
    {
        DB::table('jwt_tokens')
            ->where('access_token', $tokenString)
            ->update([
                'invalidated_at' => now(),
                'updated_at' => now()
            ]);
    }

    /**
     * 사용자의 모든 토큰 무효화
     */
    protected function invalidateAllUserTokens($accountId)
    {
        DB::table('jwt_tokens')
            ->where('account_id', $accountId)
            ->whereNull('invalidated_at')
            ->update([
                'invalidated_at' => now(),
                'updated_at' => now()
            ]);
    }

    /**
     * 로그아웃 로그 기록
     */
    protected function logSignout($accountId)
    {
        DB::table('account_logs')->insert([
            'account_id' => $accountId,
            'action' => 'jwt_signout',
            'description' => 'JWT 로그아웃',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now()
        ]);

        // 로그인 히스토리 업데이트
        DB::table('login_histories')
            ->where('account_id', $accountId)
            ->whereNull('logout_at')
            ->orderBy('login_at', 'desc')
            ->limit(1)
            ->update([
                'logout_at' => now(),
                'updated_at' => now()
            ]);
    }

    /**
     * 전체 로그아웃 로그 기록
     */
    protected function logSignoutAll($accountId)
    {
        DB::table('account_logs')->insert([
            'account_id' => $accountId,
            'action' => 'jwt_signout_all',
            'description' => 'JWT 전체 기기 로그아웃',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now()
        ]);

        // 모든 활성 세션 종료
        DB::table('login_histories')
            ->where('account_id', $accountId)
            ->whereNull('logout_at')
            ->update([
                'logout_at' => now(),
                'logout_reason' => 'signout_all',
                'updated_at' => now()
            ]);
    }
}