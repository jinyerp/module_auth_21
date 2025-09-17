<?php

namespace Jiny\Auth\App\Http\Controllers\Home;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Jenssegers\Agent\Agent;
use Carbon\Carbon;

class SessionController extends Controller
{
    /**
     * 내 활성 세션 목록
     * GET /home/account/sessions
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $currentSessionId = Session::getId();
        
        // 활성 세션 목록 조회
        $sessions = DB::table('user_sessions')
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->orderBy('last_activity', 'desc')
            ->get()
            ->map(function ($session) use ($currentSessionId) {
                $session->is_current = $session->session_id === $currentSessionId;
                $session->last_activity_human = Carbon::parse($session->last_activity)->diffForHumans();
                $session->login_at_formatted = Carbon::parse($session->login_at)->format('Y-m-d H:i');
                
                // 디바이스 정보 파싱
                if ($session->user_agent) {
                    $agent = new Agent();
                    $agent->setUserAgent($session->user_agent);
                    $session->device_info = $this->getDeviceInfo($agent);
                }
                
                return $session;
            });
        
        // 통계 정보
        $statistics = [
            'total_sessions' => $sessions->count(),
            'desktop_sessions' => $sessions->where('device_type', 'desktop')->count(),
            'mobile_sessions' => $sessions->where('device_type', 'mobile')->count(),
            'tablet_sessions' => $sessions->where('device_type', 'tablet')->count(),
        ];
        
        return view('jiny-auth::home.sessions.index', compact('sessions', 'statistics', 'currentSessionId'));
    }
    
    /**
     * 세션 상세 정보
     * GET /home/account/sessions/{id}/details
     */
    public function details(Request $request, $id)
    {
        $user = Auth::user();
        
        $session = DB::table('user_sessions')
            ->where('id', $id)
            ->where('user_id', $user->id)
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
            ];
        }
        
        // 세션 활동 로그
        $activities = DB::table('user_logs')
            ->where('user_id', $user->id)
            ->where('session_id', $session->session_id)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();
        
        return view('jiny-auth::home.sessions.details', compact('session', 'activities'));
    }
    
    /**
     * 세션 종료
     * POST /home/account/sessions/{id}/terminate
     */
    public function terminate(Request $request, $id)
    {
        $user = Auth::user();
        $currentSessionId = Session::getId();
        
        $session = DB::table('user_sessions')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->first();
        
        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => '세션을 찾을 수 없습니다.'
            ], 404);
        }
        
        // 현재 세션은 종료할 수 없음
        if ($session->session_id === $currentSessionId) {
            return response()->json([
                'success' => false,
                'message' => '현재 사용 중인 세션은 종료할 수 없습니다.'
            ], 400);
        }
        
        // 세션 종료
        DB::table('user_sessions')
            ->where('id', $id)
            ->update([
                'is_active' => false,
                'logout_at' => now(),
                'updated_at' => now()
            ]);
        
        // 실제 세션 파일 삭제 (파일 기반 세션의 경우)
        $this->destroySessionFile($session->session_id);
        
        // 활동 로그 기록
        $this->logActivity($user->id, 'session_terminated', "세션 종료: {$session->ip_address}", $request);
        
        return response()->json([
            'success' => true,
            'message' => '세션이 종료되었습니다.'
        ]);
    }
    
    /**
     * 모든 세션 종료
     * POST /home/account/sessions/terminate-all
     */
    public function terminateAll(Request $request)
    {
        $user = Auth::user();
        $currentSessionId = Session::getId();
        
        // 현재 세션을 제외한 모든 활성 세션 조회
        $sessions = DB::table('user_sessions')
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->where('session_id', '!=', $currentSessionId)
            ->get();
        
        if ($sessions->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => '종료할 다른 세션이 없습니다.'
            ]);
        }
        
        // 세션 종료
        DB::table('user_sessions')
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->where('session_id', '!=', $currentSessionId)
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
        $this->logActivity($user->id, 'all_sessions_terminated', 
            "모든 세션 종료 ({$sessions->count()}개)", $request);
        
        return response()->json([
            'success' => true,
            'message' => "{$sessions->count()}개의 세션이 종료되었습니다."
        ]);
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
        } elseif ($agent->isTablet()) {
            $info['icon'] = 'fas fa-tablet-alt';
            $info['type'] = 'Tablet';
        } elseif ($agent->isMobile()) {
            $info['icon'] = 'fas fa-mobile-alt';
            $info['type'] = 'Mobile';
        } else {
            $info['icon'] = 'fas fa-question';
            $info['type'] = 'Unknown';
        }
        
        $info['browser'] = $agent->browser();
        $info['platform'] = $agent->platform();
        
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