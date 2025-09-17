<?php

namespace Jiny\Auth\App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminEmergencyController extends Controller
{
    /**
     * 점검 모드 설정 페이지
     * GET /admin/auth/emergency/maintenance
     */
    public function maintenance()
    {
        $maintenanceMode = Cache::get('auth.maintenance_mode', [
            'enabled' => false,
            'message' => '',
            'start_time' => null,
            'end_time' => null,
            'allowed_ips' => []
        ]);
        
        return view('jiny-auth::admin.emergency.maintenance', compact('maintenanceMode'));
    }
    
    /**
     * 점검 모드 활성화/비활성화
     * POST /admin/auth/emergency/maintenance
     */
    public function toggleMaintenance(Request $request)
    {
        $request->validate([
            'enabled' => 'required|boolean',
            'message' => 'required_if:enabled,true|string|max:1000',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date|after:start_time',
            'allowed_ips' => 'nullable|string' // 쉼표로 구분된 IP 목록
        ]);
        
        if ($request->enabled) {
            // 점검 모드 활성화
            $allowedIps = array_filter(array_map('trim', explode(',', $request->allowed_ips ?? '')));
            
            // 관리자 IP 자동 추가
            $allowedIps[] = $request->ip();
            $allowedIps = array_unique($allowedIps);
            
            $maintenanceData = [
                'enabled' => true,
                'message' => $request->message,
                'start_time' => $request->start_time ?? now(),
                'end_time' => $request->end_time,
                'allowed_ips' => $allowedIps,
                'activated_by' => auth()->id(),
                'activated_at' => now()
            ];
            
            // 점검 모드 캐시에 저장 (24시간)
            Cache::put('auth.maintenance_mode', $maintenanceData, 86400);
            
            // 점검 모드 파일 생성 (Laravel 기본 점검 모드 호환)
            file_put_contents(storage_path('framework/down'), json_encode([
                'time' => Carbon::now()->timestamp,
                'message' => $request->message,
                'retry' => $request->end_time ? Carbon::parse($request->end_time)->timestamp : null,
                'allowed' => $allowedIps
            ]));
            
            // 로그 기록
            DB::table('auth_maintenance_logs')->insert([
                'action' => 'activated',
                'message' => $request->message,
                'start_time' => $maintenanceData['start_time'],
                'end_time' => $maintenanceData['end_time'],
                'performed_by' => auth()->id(),
                'created_at' => now()
            ]);
            
            // 모든 사용자 세션 종료 (관리자 제외)
            DB::table('sessions')
                ->join('users', 'sessions.user_id', '=', 'users.id')
                ->where('users.is_admin', false)
                ->delete();
            
            activity()
                ->causedBy(auth()->user())
                ->withProperties($maintenanceData)
                ->log('점검 모드 활성화');
            
            return response()->json([
                'success' => true,
                'message' => '점검 모드가 활성화되었습니다.'
            ]);
        } else {
            // 점검 모드 비활성화
            Cache::forget('auth.maintenance_mode');
            
            // 점검 모드 파일 삭제
            if (file_exists(storage_path('framework/down'))) {
                unlink(storage_path('framework/down'));
            }
            
            // 로그 기록
            DB::table('auth_maintenance_logs')->insert([
                'action' => 'deactivated',
                'message' => '점검 모드 해제',
                'performed_by' => auth()->id(),
                'created_at' => now()
            ]);
            
            activity()
                ->causedBy(auth()->user())
                ->log('점검 모드 비활성화');
            
            return response()->json([
                'success' => true,
                'message' => '점검 모드가 비활성화되었습니다.'
            ]);
        }
    }
    
    /**
     * 로그인 차단 설정 페이지
     * GET /admin/auth/emergency/block-login
     */
    public function blockLogin()
    {
        $blockLoginMode = Cache::get('auth.block_login_mode', [
            'enabled' => false,
            'reason' => '',
            'except_admins' => true,
            'allowed_users' => []
        ]);
        
        return view('jiny-auth::admin.emergency.block-login', compact('blockLoginMode'));
    }
    
    /**
     * 로그인 차단 활성화/비활성화
     * POST /admin/auth/emergency/block-login
     */
    public function toggleBlockLogin(Request $request)
    {
        $request->validate([
            'enabled' => 'required|boolean',
            'reason' => 'required_if:enabled,true|string|max:500',
            'except_admins' => 'boolean',
            'allowed_users' => 'nullable|array',
            'allowed_users.*' => 'exists:users,id'
        ]);
        
        if ($request->enabled) {
            // 로그인 차단 활성화
            $blockData = [
                'enabled' => true,
                'reason' => $request->reason,
                'except_admins' => $request->except_admins ?? true,
                'allowed_users' => $request->allowed_users ?? [],
                'activated_by' => auth()->id(),
                'activated_at' => now()
            ];
            
            // 캐시에 저장 (24시간)
            Cache::put('auth.block_login_mode', $blockData, 86400);
            
            // 현재 로그인된 일반 사용자 세션 종료
            if ($request->except_admins) {
                DB::table('sessions')
                    ->join('users', 'sessions.user_id', '=', 'users.id')
                    ->where('users.is_admin', false)
                    ->whereNotIn('users.id', $request->allowed_users ?? [])
                    ->delete();
            } else {
                // 모든 사용자 세션 종료 (허용된 사용자 제외)
                DB::table('sessions')
                    ->whereNotIn('user_id', $request->allowed_users ?? [])
                    ->delete();
            }
            
            // 로그 기록
            DB::table('auth_emergency_logs')->insert([
                'type' => 'login_blocked',
                'action' => 'activated',
                'reason' => $request->reason,
                'data' => json_encode($blockData),
                'performed_by' => auth()->id(),
                'created_at' => now()
            ]);
            
            activity()
                ->causedBy(auth()->user())
                ->withProperties($blockData)
                ->log('로그인 차단 모드 활성화');
            
            return response()->json([
                'success' => true,
                'message' => '로그인 차단이 활성화되었습니다.'
            ]);
        } else {
            // 로그인 차단 비활성화
            Cache::forget('auth.block_login_mode');
            
            // 로그 기록
            DB::table('auth_emergency_logs')->insert([
                'type' => 'login_blocked',
                'action' => 'deactivated',
                'reason' => '로그인 차단 해제',
                'performed_by' => auth()->id(),
                'created_at' => now()
            ]);
            
            activity()
                ->causedBy(auth()->user())
                ->log('로그인 차단 모드 비활성화');
            
            return response()->json([
                'success' => true,
                'message' => '로그인 차단이 비활성화되었습니다.'
            ]);
        }
    }
    
    /**
     * 긴급 알림 발송
     * POST /admin/auth/emergency/alert
     */
    public function sendAlert(Request $request)
    {
        $request->validate([
            'type' => 'required|in:email,sms,both',
            'priority' => 'required|in:low,medium,high,critical',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'target' => 'required|in:all,admins,users,specific',
            'user_ids' => 'required_if:target,specific|array',
            'user_ids.*' => 'exists:users,id'
        ]);
        
        // 대상 사용자 선택
        $query = \App\Models\User::query();
        
        switch ($request->target) {
            case 'admins':
                $query->where('is_admin', true);
                break;
            case 'users':
                $query->where('is_admin', false);
                break;
            case 'specific':
                $query->whereIn('id', $request->user_ids);
                break;
            // 'all'인 경우 모든 사용자
        }
        
        $users = $query->get();
        $sentCount = 0;
        
        foreach ($users as $user) {
            // 이메일 발송
            if (in_array($request->type, ['email', 'both'])) {
                \Mail::to($user->email)->queue(new \App\Mail\EmergencyAlert(
                    $request->subject,
                    $request->message,
                    $request->priority
                ));
            }
            
            // SMS 발송
            if (in_array($request->type, ['sms', 'both']) && $user->phone) {
                // SMS 서비스 호출
                \App\Services\SmsService::send($user->phone, $request->message);
            }
            
            $sentCount++;
        }
        
        // 긴급 알림 로그
        DB::table('auth_emergency_alerts')->insert([
            'type' => $request->type,
            'priority' => $request->priority,
            'subject' => $request->subject,
            'message' => $request->message,
            'target' => $request->target,
            'sent_count' => $sentCount,
            'sent_by' => auth()->id(),
            'created_at' => now()
        ]);
        
        activity()
            ->causedBy(auth()->user())
            ->withProperties([
                'type' => $request->type,
                'priority' => $request->priority,
                'sent_count' => $sentCount
            ])
            ->log('긴급 알림 발송');
        
        return response()->json([
            'success' => true,
            'message' => "{$sentCount}명에게 긴급 알림이 발송되었습니다.",
            'sent_count' => $sentCount
        ]);
    }
    
    /**
     * 시스템 상태 점검
     * GET /admin/auth/emergency/system-check
     */
    public function systemCheck()
    {
        $checks = [];
        
        // 데이터베이스 연결 체크
        try {
            DB::connection()->getPdo();
            $checks['database'] = [
                'status' => 'ok',
                'message' => '데이터베이스 연결 정상'
            ];
        } catch (\Exception $e) {
            $checks['database'] = [
                'status' => 'error',
                'message' => '데이터베이스 연결 실패: ' . $e->getMessage()
            ];
        }
        
        // 캐시 체크
        try {
            Cache::put('test_key', 'test_value', 1);
            $value = Cache::get('test_key');
            Cache::forget('test_key');
            
            $checks['cache'] = [
                'status' => $value === 'test_value' ? 'ok' : 'error',
                'message' => $value === 'test_value' ? '캐시 시스템 정상' : '캐시 시스템 오류'
            ];
        } catch (\Exception $e) {
            $checks['cache'] = [
                'status' => 'error',
                'message' => '캐시 시스템 오류: ' . $e->getMessage()
            ];
        }
        
        // 세션 체크
        try {
            session(['test_key' => 'test_value']);
            $value = session('test_key');
            session()->forget('test_key');
            
            $checks['session'] = [
                'status' => $value === 'test_value' ? 'ok' : 'error',
                'message' => $value === 'test_value' ? '세션 시스템 정상' : '세션 시스템 오류'
            ];
        } catch (\Exception $e) {
            $checks['session'] = [
                'status' => 'error',
                'message' => '세션 시스템 오류: ' . $e->getMessage()
            ];
        }
        
        // 디스크 공간 체크
        $diskFree = disk_free_space('/');
        $diskTotal = disk_total_space('/');
        $diskUsedPercent = round((($diskTotal - $diskFree) / $diskTotal) * 100, 2);
        
        $checks['disk'] = [
            'status' => $diskUsedPercent < 90 ? 'ok' : 'warning',
            'message' => "디스크 사용률: {$diskUsedPercent}%",
            'data' => [
                'free' => $this->formatBytes($diskFree),
                'total' => $this->formatBytes($diskTotal),
                'used_percent' => $diskUsedPercent
            ]
        ];
        
        // 메모리 사용량 체크
        $memoryUsage = memory_get_usage(true);
        $memoryLimit = ini_get('memory_limit');
        
        $checks['memory'] = [
            'status' => 'ok',
            'message' => '메모리 사용량 정상',
            'data' => [
                'usage' => $this->formatBytes($memoryUsage),
                'limit' => $memoryLimit
            ]
        ];
        
        // 활성 사용자 수
        $activeUsers = DB::table('sessions')
            ->where('last_activity', '>', now()->subMinutes(5)->timestamp)
            ->count();
        
        $checks['active_users'] = [
            'status' => 'info',
            'message' => "현재 활성 사용자: {$activeUsers}명"
        ];
        
        // 최근 에러 로그
        $recentErrors = DB::table('auth_error_logs')
            ->where('created_at', '>', now()->subHour())
            ->count();
        
        $checks['errors'] = [
            'status' => $recentErrors > 100 ? 'warning' : 'ok',
            'message' => "최근 1시간 에러: {$recentErrors}건"
        ];
        
        return response()->json([
            'success' => true,
            'timestamp' => now(),
            'checks' => $checks
        ]);
    }
    
    /**
     * 모든 세션 종료
     * POST /admin/auth/emergency/kill-all-sessions
     */
    public function killAllSessions(Request $request)
    {
        $request->validate([
            'except_current' => 'boolean',
            'admin_password' => 'required' // 관리자 비밀번호 확인
        ]);
        
        // 관리자 비밀번호 확인
        if (!\Hash::check($request->admin_password, auth()->user()->password)) {
            return response()->json([
                'success' => false,
                'message' => '관리자 비밀번호가 일치하지 않습니다.'
            ], 401);
        }
        
        // 세션 종료
        $query = DB::table('sessions');
        
        if ($request->except_current) {
            $query->where('id', '!=', session()->getId());
        }
        
        $killedCount = $query->delete();
        
        // 사용자 세션 테이블도 업데이트
        DB::table('auth_user_sessions')
            ->whereNull('logged_out_at')
            ->update(['logged_out_at' => now()]);
        
        // 로그 기록
        DB::table('auth_emergency_logs')->insert([
            'type' => 'kill_sessions',
            'action' => 'executed',
            'reason' => '모든 세션 강제 종료',
            'data' => json_encode(['killed_count' => $killedCount]),
            'performed_by' => auth()->id(),
            'created_at' => now()
        ]);
        
        activity()
            ->causedBy(auth()->user())
            ->withProperties(['killed_count' => $killedCount])
            ->log('모든 세션 강제 종료');
        
        return response()->json([
            'success' => true,
            'message' => "{$killedCount}개의 세션이 종료되었습니다.",
            'killed_count' => $killedCount
        ]);
    }
    
    /**
     * 바이트를 읽기 쉬운 형식으로 변환
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        return round($bytes / (1 << (10 * $pow)), $precision) . ' ' . $units[$pow];
    }
}