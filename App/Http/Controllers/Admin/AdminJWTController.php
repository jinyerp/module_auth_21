<?php

namespace Jiny\Auth\App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\User;
use Carbon\Carbon;

class AdminJWTController extends Controller
{
    /**
     * 전체 토큰 목록
     * GET /admin/auth/jwt/tokens
     */
    public function index(Request $request)
    {
        $query = DB::table('jwt_tokens')
            ->join('users', 'jwt_tokens.user_id', '=', 'users.id')
            ->select('jwt_tokens.*', 'users.name', 'users.email');
        
        // 검색 필터
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('users.name', 'like', "%{$search}%")
                  ->orWhere('users.email', 'like', "%{$search}%")
                  ->orWhere('jwt_tokens.name', 'like', "%{$search}%")
                  ->orWhere('jwt_tokens.device', 'like', "%{$search}%");
            });
        }
        
        // 상태 필터
        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->whereNull('revoked_at')
                      ->where(function($q) {
                          $q->whereNull('expires_at')
                            ->orWhere('expires_at', '>', now());
                      });
            } elseif ($request->status === 'expired') {
                $query->whereNotNull('expires_at')
                      ->where('expires_at', '<', now());
            } elseif ($request->status === 'revoked') {
                $query->whereNotNull('revoked_at');
            }
        }
        
        // 토큰 타입 필터
        if ($request->has('token_type') && $request->token_type) {
            $query->where('token_type', $request->token_type);
        }
        
        $tokens = $query->orderBy('last_used_at', 'desc')->paginate(20);
        
        // 토큰 정보 가공
        foreach ($tokens as $token) {
            $token->is_expired = $token->expires_at && Carbon::parse($token->expires_at)->isPast();
            $token->is_revoked = (bool) $token->revoked_at;
            $token->is_active = !$token->is_expired && !$token->is_revoked;
            $token->last_used_human = $token->last_used_at 
                ? Carbon::parse($token->last_used_at)->diffForHumans() 
                : 'Never';
            $token->created_at_formatted = Carbon::parse($token->created_at)->format('Y-m-d H:i');
        }
        
        // 통계 정보
        $statistics = $this->getStatistics();
        
        return view('jiny-auth::admin.jwt.index', compact('tokens', 'statistics'));
    }
    
    /**
     * 활성 토큰 목록
     * GET /admin/auth/jwt/tokens/active
     */
    public function active(Request $request)
    {
        $tokens = DB::table('jwt_tokens')
            ->join('users', 'jwt_tokens.user_id', '=', 'users.id')
            ->select('jwt_tokens.*', 'users.name', 'users.email')
            ->whereNull('jwt_tokens.revoked_at')
            ->where(function($query) {
                $query->whereNull('jwt_tokens.expires_at')
                      ->orWhere('jwt_tokens.expires_at', '>', now());
            })
            ->orderBy('jwt_tokens.last_used_at', 'desc')
            ->paginate(20);
        
        foreach ($tokens as $token) {
            $token->is_active = true;
            $token->last_used_human = $token->last_used_at 
                ? Carbon::parse($token->last_used_at)->diffForHumans() 
                : 'Never';
            $token->created_at_formatted = Carbon::parse($token->created_at)->format('Y-m-d H:i');
        }
        
        return view('jiny-auth::admin.jwt.active', compact('tokens'));
    }
    
    /**
     * 만료된 토큰 목록
     * GET /admin/auth/jwt/tokens/expired
     */
    public function expired(Request $request)
    {
        $tokens = DB::table('jwt_tokens')
            ->join('users', 'jwt_tokens.user_id', '=', 'users.id')
            ->select('jwt_tokens.*', 'users.name', 'users.email')
            ->whereNotNull('jwt_tokens.expires_at')
            ->where('jwt_tokens.expires_at', '<', now())
            ->orderBy('jwt_tokens.expires_at', 'desc')
            ->paginate(20);
        
        foreach ($tokens as $token) {
            $token->is_expired = true;
            $token->expired_human = Carbon::parse($token->expires_at)->diffForHumans();
            $token->created_at_formatted = Carbon::parse($token->created_at)->format('Y-m-d H:i');
        }
        
        return view('jiny-auth::admin.jwt.expired', compact('tokens'));
    }
    
    /**
     * 토큰 상세 정보
     * GET /admin/auth/jwt/tokens/{id}
     */
    public function show(Request $request, $id)
    {
        $token = DB::table('jwt_tokens')
            ->join('users', 'jwt_tokens.user_id', '=', 'users.id')
            ->select('jwt_tokens.*', 'users.name as user_name', 'users.email')
            ->where('jwt_tokens.id', $id)
            ->first();
        
        if (!$token) {
            return response()->json(['error' => '토큰을 찾을 수 없습니다.'], 404);
        }
        
        // 토큰 사용 이력
        $logs = DB::table('jwt_token_logs')
            ->where('token_id', $id)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();
        
        foreach ($logs as $log) {
            $log->created_at_formatted = Carbon::parse($log->created_at)->format('Y-m-d H:i:s');
            $log->created_at_human = Carbon::parse($log->created_at)->diffForHumans();
        }
        
        // 토큰 클레임 정보 (JWT 페이로드)
        $claims = json_decode($token->claims ?? '{}', true);
        
        return view('jiny-auth::admin.jwt.show', compact('token', 'logs', 'claims'));
    }
    
    /**
     * 토큰 강제 삭제
     * DELETE /admin/auth/jwt/tokens/{id}
     */
    public function destroy(Request $request, $id)
    {
        $token = DB::table('jwt_tokens')->find($id);
        
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
                'revoked_by' => Auth::id(),
                'revoked_reason' => 'Admin revoked',
                'updated_at' => now()
            ]);
        
        // 활동 로그 기록
        $this->logActivity($token->user_id, 'token_admin_revoked', 
            "관리자에 의한 JWT 토큰 무효화", $request);
        
        return response()->json([
            'success' => true,
            'message' => '토큰이 무효화되었습니다.'
        ]);
    }
    
    /**
     * 모든 토큰 무효화
     * POST /admin/auth/jwt/tokens/revoke-all
     */
    public function revokeAll(Request $request)
    {
        // 활성 토큰 개수 확인
        $activeTokensCount = DB::table('jwt_tokens')
            ->whereNull('revoked_at')
            ->where(function($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            })
            ->count();
        
        if ($activeTokensCount == 0) {
            return response()->json([
                'success' => false,
                'message' => '무효화할 토큰이 없습니다.'
            ]);
        }
        
        // 모든 토큰 무효화
        DB::table('jwt_tokens')
            ->whereNull('revoked_at')
            ->update([
                'revoked_at' => now(),
                'revoked_by' => Auth::id(),
                'revoked_reason' => 'Admin revoked all tokens',
                'updated_at' => now()
            ]);
        
        // 활동 로그 기록
        $this->logActivity(null, 'all_tokens_admin_revoked', 
            "관리자에 의한 모든 JWT 토큰 무효화 ({$activeTokensCount}개)", $request);
        
        return response()->json([
            'success' => true,
            'message' => "{$activeTokensCount}개의 토큰이 무효화되었습니다."
        ]);
    }
    
    /**
     * 사용자 토큰 무효화
     * POST /admin/auth/jwt/tokens/revoke-user/{id}
     */
    public function revokeUser(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        // 사용자의 활성 토큰 개수 확인
        $activeTokensCount = DB::table('jwt_tokens')
            ->where('user_id', $user->id)
            ->whereNull('revoked_at')
            ->where(function($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            })
            ->count();
        
        if ($activeTokensCount == 0) {
            return response()->json([
                'success' => false,
                'message' => '무효화할 토큰이 없습니다.'
            ]);
        }
        
        // 사용자 토큰 모두 무효화
        DB::table('jwt_tokens')
            ->where('user_id', $user->id)
            ->whereNull('revoked_at')
            ->update([
                'revoked_at' => now(),
                'revoked_by' => Auth::id(),
                'revoked_reason' => 'Admin revoked user tokens',
                'updated_at' => now()
            ]);
        
        // 활동 로그 기록
        $this->logActivity($user->id, 'user_tokens_admin_revoked', 
            "관리자에 의한 사용자 토큰 무효화 ({$activeTokensCount}개)", $request);
        
        return response()->json([
            'success' => true,
            'message' => "{$user->name}의 {$activeTokensCount}개 토큰이 무효화되었습니다."
        ]);
    }
    
    /**
     * JWT 설정 관리
     * GET /admin/auth/jwt/settings
     */
    public function settings(Request $request)
    {
        // JWT 설정 가져오기
        $settings = [
            'ttl' => config('jwt.ttl', 60),
            'refresh_ttl' => config('jwt.refresh_ttl', 20160),
            'blacklist_enabled' => config('jwt.blacklist_enabled', true),
            'blacklist_grace_period' => config('jwt.blacklist_grace_period', 0),
            'algo' => config('jwt.algo', 'HS256'),
            'max_tokens_per_user' => Cache::get('jwt_max_tokens_per_user', 5),
            'auto_cleanup_expired' => Cache::get('jwt_auto_cleanup_expired', true),
            'cleanup_days' => Cache::get('jwt_cleanup_days', 30),
        ];
        
        return view('jiny-auth::admin.jwt.settings', compact('settings'));
    }
    
    /**
     * JWT 설정 업데이트
     * POST /admin/auth/jwt/settings
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'max_tokens_per_user' => 'required|integer|min:1|max:100',
            'auto_cleanup_expired' => 'required|boolean',
            'cleanup_days' => 'required|integer|min:1|max:365',
        ]);
        
        // 설정 저장 (캐시에 저장, 실제 환경에서는 config 파일에 저장)
        Cache::put('jwt_max_tokens_per_user', $request->max_tokens_per_user, now()->addYear());
        Cache::put('jwt_auto_cleanup_expired', $request->auto_cleanup_expired, now()->addYear());
        Cache::put('jwt_cleanup_days', $request->cleanup_days, now()->addYear());
        
        // 활동 로그 기록
        $this->logActivity(null, 'jwt_settings_updated', 
            "JWT 설정 변경", $request);
        
        return redirect()->route('admin.auth.jwt.settings')
            ->with('success', 'JWT 설정이 업데이트되었습니다.');
    }
    
    /**
     * JWT 사용 통계
     * GET /admin/auth/jwt/statistics
     */
    public function statistics(Request $request)
    {
        $statistics = $this->getStatistics();
        
        // 시간대별 토큰 생성 (최근 24시간)
        $hourly = [];
        for ($i = 23; $i >= 0; $i--) {
            $hour = now()->subHours($i);
            $hourly[$hour->format('H:00')] = DB::table('jwt_tokens')
                ->where('created_at', '>=', $hour->startOfHour())
                ->where('created_at', '<', $hour->copy()->endOfHour())
                ->count();
        }
        
        // 일별 토큰 생성 (최근 30일)
        $daily = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $daily[$date] = [
                'created' => DB::table('jwt_tokens')
                    ->whereDate('created_at', $date)
                    ->count(),
                'revoked' => DB::table('jwt_tokens')
                    ->whereDate('revoked_at', $date)
                    ->count(),
            ];
        }
        
        // 토큰 타입별 통계
        $byType = DB::table('jwt_tokens')
            ->select('token_type', DB::raw('COUNT(*) as count'))
            ->groupBy('token_type')
            ->get()
            ->pluck('count', 'token_type')
            ->toArray();
        
        // 사용자별 토큰 통계 (상위 10명)
        $topUsers = DB::table('jwt_tokens')
            ->join('users', 'jwt_tokens.user_id', '=', 'users.id')
            ->select('users.id', 'users.name', 'users.email', DB::raw('COUNT(jwt_tokens.id) as token_count'))
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderBy('token_count', 'desc')
            ->limit(10)
            ->get();
        
        // 평균 토큰 수명
        $avgLifetime = DB::table('jwt_tokens')
            ->whereNotNull('revoked_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, revoked_at)) as avg_minutes')
            ->first();
        
        $statistics['avg_lifetime_minutes'] = $avgLifetime->avg_minutes ?? 0;
        $statistics['avg_lifetime_human'] = $avgLifetime->avg_minutes 
            ? $this->minutesToHuman($avgLifetime->avg_minutes)
            : 'N/A';
        
        return view('jiny-auth::admin.jwt.statistics', compact('statistics', 'hourly', 'daily', 'byType', 'topUsers'));
    }
    
    /**
     * 통계 정보 가져오기
     */
    private function getStatistics()
    {
        return [
            'total_tokens' => DB::table('jwt_tokens')->count(),
            'active_tokens' => DB::table('jwt_tokens')
                ->whereNull('revoked_at')
                ->where(function($query) {
                    $query->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                })
                ->count(),
            'expired_tokens' => DB::table('jwt_tokens')
                ->whereNotNull('expires_at')
                ->where('expires_at', '<', now())
                ->count(),
            'revoked_tokens' => DB::table('jwt_tokens')
                ->whereNotNull('revoked_at')
                ->count(),
            'unique_users' => DB::table('jwt_tokens')
                ->distinct('user_id')
                ->count('user_id'),
            'recent_24h' => DB::table('jwt_tokens')
                ->where('created_at', '>=', now()->subDay())
                ->count(),
        ];
    }
    
    /**
     * 분을 사람이 읽기 쉬운 형식으로 변환
     */
    private function minutesToHuman($minutes)
    {
        if ($minutes < 60) {
            return round($minutes) . ' 분';
        } elseif ($minutes < 1440) {
            return round($minutes / 60, 1) . ' 시간';
        } else {
            return round($minutes / 1440, 1) . ' 일';
        }
    }
    
    /**
     * 활동 로그 기록
     */
    private function logActivity($userId, $action, $description, $request)
    {
        if (\Illuminate\Support\Facades\Schema::hasTable('user_logs')) {
            DB::table('user_logs')->insert([
                'user_id' => $userId,
                'admin_id' => Auth::id(),
                'action' => $action,
                'description' => $description,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now()
            ]);
        }
    }
}