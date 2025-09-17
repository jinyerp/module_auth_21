<?php

namespace Jiny\Auth\App\Http\Controllers\Home;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TokenController extends Controller
{
    /**
     * 내 토큰 목록
     * GET /home/account/tokens
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // JWT 토큰 목록 조회
        $tokens = DB::table('jwt_tokens')
            ->where('user_id', $user->id)
            ->orderBy('last_used_at', 'desc')
            ->paginate(10);
        
        // 토큰 정보 가공
        foreach ($tokens as $token) {
            $token->is_expired = $token->expires_at && Carbon::parse($token->expires_at)->isPast();
            $token->is_revoked = (bool) $token->revoked_at;
            $token->is_active = !$token->is_expired && !$token->is_revoked;
            $token->last_used_human = $token->last_used_at 
                ? Carbon::parse($token->last_used_at)->diffForHumans() 
                : 'Never';
            $token->created_at_formatted = Carbon::parse($token->created_at)->format('Y-m-d H:i');
            $token->expires_at_formatted = $token->expires_at 
                ? Carbon::parse($token->expires_at)->format('Y-m-d H:i')
                : 'Never';
        }
        
        // 통계 정보
        $statistics = [
            'total_tokens' => DB::table('jwt_tokens')->where('user_id', $user->id)->count(),
            'active_tokens' => DB::table('jwt_tokens')
                ->where('user_id', $user->id)
                ->whereNull('revoked_at')
                ->where(function($query) {
                    $query->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                })
                ->count(),
            'expired_tokens' => DB::table('jwt_tokens')
                ->where('user_id', $user->id)
                ->whereNotNull('expires_at')
                ->where('expires_at', '<', now())
                ->count(),
            'revoked_tokens' => DB::table('jwt_tokens')
                ->where('user_id', $user->id)
                ->whereNotNull('revoked_at')
                ->count(),
        ];
        
        return view('jiny-auth::home.tokens.index', compact('tokens', 'statistics'));
    }
    
    /**
     * 활성 토큰 목록
     * GET /home/account/tokens/active
     */
    public function active(Request $request)
    {
        $user = Auth::user();
        
        // 활성 토큰만 조회
        $tokens = DB::table('jwt_tokens')
            ->where('user_id', $user->id)
            ->whereNull('revoked_at')
            ->where(function($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            })
            ->orderBy('last_used_at', 'desc')
            ->paginate(10);
        
        // 토큰 정보 가공
        foreach ($tokens as $token) {
            $token->is_active = true;
            $token->last_used_human = $token->last_used_at 
                ? Carbon::parse($token->last_used_at)->diffForHumans() 
                : 'Never';
            $token->created_at_formatted = Carbon::parse($token->created_at)->format('Y-m-d H:i');
            $token->expires_at_formatted = $token->expires_at 
                ? Carbon::parse($token->expires_at)->format('Y-m-d H:i')
                : 'Never';
            
            // 남은 시간 계산
            if ($token->expires_at) {
                $expiresAt = Carbon::parse($token->expires_at);
                $token->time_remaining = $expiresAt->diffForHumans();
            } else {
                $token->time_remaining = 'Permanent';
            }
        }
        
        return view('jiny-auth::home.tokens.active', compact('tokens'));
    }
    
    /**
     * 토큰 삭제
     * DELETE /home/account/tokens/{id}
     */
    public function destroy(Request $request, $id)
    {
        $user = Auth::user();
        
        // 본인 토큰인지 확인
        $token = DB::table('jwt_tokens')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->first();
        
        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => '토큰을 찾을 수 없습니다.'
            ], 404);
        }
        
        // 토큰 무효화
        DB::table('jwt_tokens')
            ->where('id', $id)
            ->update([
                'revoked_at' => now(),
                'revoked_by' => $user->id,
                'revoked_reason' => 'User revoked',
                'updated_at' => now()
            ]);
        
        // 활동 로그 기록
        $this->logActivity($user->id, 'token_revoked', "JWT 토큰 무효화: {$token->name}", $request);
        
        return response()->json([
            'success' => true,
            'message' => '토큰이 무효화되었습니다.'
        ]);
    }
    
    /**
     * 모든 토큰 무효화
     * POST /home/account/tokens/revoke-all
     */
    public function revokeAll(Request $request)
    {
        $user = Auth::user();
        $currentToken = $request->bearerToken();
        
        // 현재 토큰 정보 조회 (현재 토큰은 유지)
        $currentTokenRecord = null;
        if ($currentToken) {
            $currentTokenRecord = DB::table('jwt_tokens')
                ->where('user_id', $user->id)
                ->where('token', hash('sha256', $currentToken))
                ->first();
        }
        
        // 활성 토큰 개수 확인
        $activeTokensQuery = DB::table('jwt_tokens')
            ->where('user_id', $user->id)
            ->whereNull('revoked_at')
            ->where(function($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            });
        
        if ($currentTokenRecord) {
            $activeTokensQuery->where('id', '!=', $currentTokenRecord->id);
        }
        
        $activeTokensCount = $activeTokensQuery->count();
        
        if ($activeTokensCount == 0) {
            return response()->json([
                'success' => false,
                'message' => '무효화할 다른 토큰이 없습니다.'
            ]);
        }
        
        // 모든 토큰 무효화 (현재 토큰 제외)
        $query = DB::table('jwt_tokens')
            ->where('user_id', $user->id)
            ->whereNull('revoked_at');
        
        if ($currentTokenRecord) {
            $query->where('id', '!=', $currentTokenRecord->id);
        }
        
        $query->update([
            'revoked_at' => now(),
            'revoked_by' => $user->id,
            'revoked_reason' => 'User revoked all tokens',
            'updated_at' => now()
        ]);
        
        // 활동 로그 기록
        $this->logActivity($user->id, 'all_tokens_revoked', 
            "모든 JWT 토큰 무효화 ({$activeTokensCount}개)", $request);
        
        return response()->json([
            'success' => true,
            'message' => "{$activeTokensCount}개의 토큰이 무효화되었습니다."
        ]);
    }
    
    /**
     * 토큰 사용 이력
     * GET /home/account/tokens/history
     */
    public function history(Request $request)
    {
        $user = Auth::user();
        
        // 토큰별 최근 사용 이력
        $history = DB::table('jwt_token_logs')
            ->join('jwt_tokens', 'jwt_token_logs.token_id', '=', 'jwt_tokens.id')
            ->where('jwt_tokens.user_id', $user->id)
            ->select(
                'jwt_token_logs.*',
                'jwt_tokens.name as token_name',
                'jwt_tokens.token_type',
                'jwt_tokens.device'
            )
            ->orderBy('jwt_token_logs.created_at', 'desc')
            ->paginate(50);
        
        // 이력 정보 가공
        foreach ($history as $log) {
            $log->created_at_formatted = Carbon::parse($log->created_at)->format('Y-m-d H:i:s');
            $log->created_at_human = Carbon::parse($log->created_at)->diffForHumans();
            
            // 액션 타입별 아이콘 및 색상
            switch ($log->action) {
                case 'created':
                    $log->action_icon = 'fas fa-plus-circle';
                    $log->action_color = 'success';
                    $log->action_text = '생성';
                    break;
                case 'used':
                    $log->action_icon = 'fas fa-check-circle';
                    $log->action_color = 'info';
                    $log->action_text = '사용';
                    break;
                case 'refreshed':
                    $log->action_icon = 'fas fa-sync-alt';
                    $log->action_color = 'warning';
                    $log->action_text = '갱신';
                    break;
                case 'revoked':
                    $log->action_icon = 'fas fa-times-circle';
                    $log->action_color = 'danger';
                    $log->action_text = '무효화';
                    break;
                case 'expired':
                    $log->action_icon = 'fas fa-clock';
                    $log->action_color = 'secondary';
                    $log->action_text = '만료';
                    break;
                default:
                    $log->action_icon = 'fas fa-circle';
                    $log->action_color = 'secondary';
                    $log->action_text = $log->action;
            }
        }
        
        // 일별 사용 통계
        $dailyStats = DB::table('jwt_token_logs')
            ->join('jwt_tokens', 'jwt_token_logs.token_id', '=', 'jwt_tokens.id')
            ->where('jwt_tokens.user_id', $user->id)
            ->where('jwt_token_logs.created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(jwt_token_logs.created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();
        
        return view('jiny-auth::home.tokens.history', compact('history', 'dailyStats'));
    }
    
    /**
     * 활동 로그 기록
     */
    private function logActivity($userId, $action, $description, $request)
    {
        if (\Illuminate\Support\Facades\Schema::hasTable('user_logs')) {
            DB::table('user_logs')->insert([
                'user_id' => $userId,
                'action' => $action,
                'description' => $description,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now()
            ]);
        }
    }
}