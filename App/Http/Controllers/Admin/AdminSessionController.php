<?php

namespace Jiny\Auth\App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Models\User;
use Jenssegers\Agent\Agent;
use Carbon\Carbon;

class AdminSessionController extends Controller
{
    /**
     * 전체 활성 세션 목록
     * GET /admin/auth/sessions
     */
    public function index(Request $request)
    {
        $query = DB::table('user_sessions')
            ->join('users', 'user_sessions.user_id', '=', 'users.id')
            ->select('user_sessions.*', 'users.name', 'users.email')
            ->where('user_sessions.is_active', true);
        
        // 검색 필터
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('users.name', 'like', "%{$search}%")
                  ->orWhere('users.email', 'like', "%{$search}%")
                  ->orWhere('user_sessions.ip_address', 'like', "%{$search}%");
            });
        }
        
        // 디바이스 타입 필터
        if ($request->has('device_type') && $request->device_type) {
            $query->where('user_sessions.device_type', $request->device_type);
        }
        
        // 정렬
        $sortBy = $request->get('sort_by', 'last_activity');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);
        
        $sessions = $query->paginate(20);
        
        // 세션 정보 가공
        foreach ($sessions as $session) {
            $session->last_activity_human = Carbon::parse($session->last_activity)->diffForHumans();
            $session->login_at_formatted = Carbon::parse($session->login_at)->format('Y-m-d H:i');
            $session->duration = Carbon::parse($session->login_at)->diffForHumans(null, true);
            
            // 디바이스 정보 파싱
            if ($session->user_agent) {
                $agent = new Agent();
                $agent->setUserAgent($session->user_agent);
                $session->device_info = $this->getDeviceInfo($agent);
            }
        }
        
        // 통계 정보
        $statistics = [
            'total_sessions' => DB::table('user_sessions')->where('is_active', true)->count(),
            'unique_users' => DB::table('user_sessions')->where('is_active', true)->distinct('user_id')->count('user_id'),
            'desktop_sessions' => DB::table('user_sessions')->where('is_active', true)->where('device_type', 'desktop')->count(),
            'mobile_sessions' => DB::table('user_sessions')->where('is_active', true)->where('device_type', 'mobile')->count(),
            'tablet_sessions' => DB::table('user_sessions')->where('is_active', true)->where('device_type', 'tablet')->count(),
            'last_hour_sessions' => DB::table('user_sessions')
                ->where('is_active', true)
                ->where('last_activity', '>=', now()->subHour())
                ->count(),
        ];
        
        return view('jiny-auth::admin.sessions.index', compact('sessions', 'statistics'));
    }
    
    /**
     * 세션 상세 정보
     * GET /admin/auth/sessions/{id}/details
     */
    public function details(Request $request, $id)
    {
        $session = DB::table('user_sessions')
            ->join('users', 'user_sessions.user_id', '=', 'users.id')
            ->select('user_sessions.*', 'users.name', 'users.email')
            ->where('user_sessions.id', $id)
            ->first();
        
        if (!$session) {
            return response()->json(['error' => '세션을 찾을 수 없습니다.'], 404);
        }
        
        // User-Agent 파싱
        if ($session->user_agent) {
            $agent = new Agent();
            $agent->setUserAgent($session->user_agent);
            $session->device_details = [
                'browser' => $agent->browser(),
                'browser_version' => $agent->version($agent->browser()),
                'platform' => $agent->platform(),
                'platform_version' => $agent->version($agent->platform()),
                'device' => $agent->device(),
                'is_mobile' => $agent->isMobile(),
                'is_tablet' => $agent->isTablet(),
                'is_desktop' => $agent->isDesktop(),
                'is_robot' => $agent->isRobot(),
                'languages' => $agent->languages(),
            ];
        }
        
        // 세션 활동 로그
        $activities = DB::table('user_logs')
            ->where('user_id', $session->user_id)
            ->where('session_id', $session->session_id)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();
        
        // 같은 사용자의 다른 세션
        $otherSessions = DB::table('user_sessions')
            ->where('user_id', $session->user_id)
            ->where('id', '!=', $id)
            ->where('is_active', true)
            ->orderBy('last_activity', 'desc')
            ->limit(5)
            ->get();
        
        return view('jiny-auth::admin.sessions.details', compact('session', 'activities', 'otherSessions'));
    }
    
    /**
     * 세션 강제 종료
     * POST /admin/auth/sessions/{id}/terminate
     */
    public function terminate(Request $request, $id)
    {
        $session = DB::table('user_sessions')
            ->where('id', $id)
            ->where('is_active', true)
            ->first();
        
        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => '세션을 찾을 수 없습니다.'
            ], 404);
        }
        
        // 세션 종료
        DB::table('user_sessions')
            ->where('id', $id)
            ->update([
                'is_active' => false,
                'logout_at' => now(),
                'updated_at' => now()
            ]);
        
        // 실제 세션 파일 삭제
        $this->destroySessionFile($session->session_id);
        
        // 활동 로그 기록
        $this->logActivity($session->user_id, 'session_terminated_by_admin', 
            "관리자에 의한 세션 종료", $request);
        
        return response()->json([
            'success' => true,
            'message' => '세션이 종료되었습니다.'
        ]);
    }
    
    /**
     * 일괄 세션 종료
     * POST /admin/auth/sessions/bulk-terminate
     */
    public function bulkTerminate(Request $request)
    {
        $request->validate([
            'session_ids' => 'required|array',
            'session_ids.*' => 'integer|exists:user_sessions,id'
        ]);
        
        $sessions = DB::table('user_sessions')
            ->whereIn('id', $request->session_ids)
            ->where('is_active', true)
            ->get();
        
        if ($sessions->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => '종료할 활성 세션이 없습니다.'
            ]);
        }
        
        // 세션 종료
        DB::table('user_sessions')
            ->whereIn('id', $request->session_ids)
            ->where('is_active', true)
            ->update([
                'is_active' => false,
                'logout_at' => now(),
                'updated_at' => now()
            ]);
        
        // 실제 세션 파일 삭제
        foreach ($sessions as $session) {
            $this->destroySessionFile($session->session_id);
        }
        
        // 활동 로그 기록
        foreach ($sessions as $session) {
            $this->logActivity($session->user_id, 'session_bulk_terminated_by_admin', 
                "관리자에 의한 일괄 세션 종료", $request);
        }
        
        return response()->json([
            'success' => true,
            'message' => "{$sessions->count()}개의 세션이 종료되었습니다."
        ]);
    }
    
    /**
     * 세션 통계
     * GET /admin/auth/sessions/statistics
     */
    public function statistics(Request $request)
    {
        // 기본 통계
        $statistics = [
            'current' => [
                'total_sessions' => DB::table('user_sessions')->where('is_active', true)->count(),
                'unique_users' => DB::table('user_sessions')->where('is_active', true)->distinct('user_id')->count('user_id'),
                'desktop' => DB::table('user_sessions')->where('is_active', true)->where('device_type', 'desktop')->count(),
                'mobile' => DB::table('user_sessions')->where('is_active', true)->where('device_type', 'mobile')->count(),
                'tablet' => DB::table('user_sessions')->where('is_active', true)->where('device_type', 'tablet')->count(),
            ],
            'hourly' => [],
            'daily' => [],
            'browsers' => [],
            'platforms' => [],
            'countries' => [],
        ];
        
        // 시간대별 세션 (최근 24시간)
        for ($i = 23; $i >= 0; $i--) {
            $hour = now()->subHours($i);
            $statistics['hourly'][$hour->format('H:00')] = DB::table('user_sessions')
                ->where('is_active', true)
                ->where('last_activity', '>=', $hour->startOfHour())
                ->where('last_activity', '<', $hour->copy()->endOfHour())
                ->count();
        }
        
        // 일별 세션 (최근 30일)
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $statistics['daily'][$date] = [
                'logins' => DB::table('user_sessions')
                    ->whereDate('login_at', $date)
                    ->count(),
                'active' => DB::table('user_sessions')
                    ->where('is_active', true)
                    ->whereDate('last_activity', $date)
                    ->count(),
            ];
        }
        
        // 브라우저별 통계
        $browserStats = DB::table('user_sessions')
            ->where('is_active', true)
            ->whereNotNull('browser')
            ->select('browser', DB::raw('COUNT(*) as count'))
            ->groupBy('browser')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();
        
        foreach ($browserStats as $stat) {
            $statistics['browsers'][$stat->browser] = $stat->count;
        }
        
        // 플랫폼별 통계
        $platformStats = DB::table('user_sessions')
            ->where('is_active', true)
            ->whereNotNull('platform')
            ->select('platform', DB::raw('COUNT(*) as count'))
            ->groupBy('platform')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();
        
        foreach ($platformStats as $stat) {
            $statistics['platforms'][$stat->platform] = $stat->count;
        }
        
        // 평균 세션 시간
        $avgSessionTime = DB::table('user_sessions')
            ->where('is_active', false)
            ->whereNotNull('logout_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, login_at, logout_at)) as avg_minutes')
            ->first();
        
        $statistics['average_session_duration'] = $avgSessionTime->avg_minutes ?? 0;
        
        // 피크 시간대
        $peakHour = DB::table('user_sessions')
            ->selectRaw('HOUR(login_at) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->orderBy('count', 'desc')
            ->first();
        
        $statistics['peak_hour'] = $peakHour ? $peakHour->hour . ':00' : 'N/A';
        
        if ($request->wantsJson()) {
            return response()->json($statistics);
        }
        
        return view('jiny-auth::admin.sessions.statistics', compact('statistics'));
    }
    
    /**
     * 디바이스 정보 추출
     */
    private function getDeviceInfo(Agent $agent)
    {
        $info = [];
        
        if ($agent->isDesktop()) {
            $info['icon'] = 'fas fa-desktop';
            $info['type'] = 'Desktop';
            $info['color'] = 'primary';
        } elseif ($agent->isTablet()) {
            $info['icon'] = 'fas fa-tablet-alt';
            $info['type'] = 'Tablet';
            $info['color'] = 'info';
        } elseif ($agent->isMobile()) {
            $info['icon'] = 'fas fa-mobile-alt';
            $info['type'] = 'Mobile';
            $info['color'] = 'success';
        } elseif ($agent->isRobot()) {
            $info['icon'] = 'fas fa-robot';
            $info['type'] = 'Bot';
            $info['color'] = 'warning';
        } else {
            $info['icon'] = 'fas fa-question';
            $info['type'] = 'Unknown';
            $info['color'] = 'secondary';
        }
        
        $info['browser'] = $agent->browser() ?: 'Unknown';
        $info['platform'] = $agent->platform() ?: 'Unknown';
        
        return $info;
    }
    
    /**
     * 세션 파일 삭제
     */
    private function destroySessionFile($sessionId)
    {
        $driver = config('session.driver');
        
        if ($driver === 'file') {
            $path = config('session.files') . '/' . $sessionId;
            if (file_exists($path)) {
                unlink($path);
            }
        } elseif ($driver === 'redis') {
            // Redis 세션 삭제
            \Illuminate\Support\Facades\Redis::del('laravel_session:' . $sessionId);
        }
        // 다른 드라이버에 대한 처리 추가 가능
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
                'session_id' => Session::getId(),
                'created_at' => now()
            ]);
        }
    }
}