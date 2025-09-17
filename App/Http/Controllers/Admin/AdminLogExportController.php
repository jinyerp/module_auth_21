<?php

namespace Jiny\Auth\App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class AdminLogExportController extends Controller
{
    /**
     * 로그인 히스토리 내보내기
     * GET /admin/auth/login-history/export
     */
    public function exportLoginHistory(Request $request)
    {
        // 필터 조건
        $query = DB::table('auth_login_histories')
            ->join('users', 'auth_login_histories.user_id', '=', 'users.id')
            ->select(
                'auth_login_histories.*',
                'users.name as user_name',
                'users.email as user_email'
            );
        
        // 날짜 필터
        if ($request->has('date_from')) {
            $query->whereDate('auth_login_histories.created_at', '>=', $request->get('date_from'));
        }
        
        if ($request->has('date_to')) {
            $query->whereDate('auth_login_histories.created_at', '<=', $request->get('date_to'));
        }
        
        // 상태 필터
        if ($request->has('status')) {
            $query->where('auth_login_histories.status', $request->get('status'));
        }
        
        $logs = $query->orderBy('auth_login_histories.created_at', 'desc')->get();
        
        // 내보내기 형식
        $format = $request->get('format', 'csv');
        
        if ($format === 'json') {
            return $this->exportAsJson($logs, 'login_history');
        } else {
            return $this->exportAsCsv($logs, 'login_history');
        }
    }
    
    /**
     * 활동 로그 내보내기
     * GET /admin/auth/account-logs/export
     */
    public function exportAccountLogs(Request $request)
    {
        $query = DB::table('auth_account_logs')
            ->join('users', 'auth_account_logs.user_id', '=', 'users.id')
            ->select(
                'auth_account_logs.*',
                'users.name as user_name',
                'users.email as user_email'
            );
        
        // 날짜 필터
        if ($request->has('date_from')) {
            $query->whereDate('auth_account_logs.created_at', '>=', $request->get('date_from'));
        }
        
        if ($request->has('date_to')) {
            $query->whereDate('auth_account_logs.created_at', '<=', $request->get('date_to'));
        }
        
        // 이벤트 필터
        if ($request->has('event')) {
            $query->where('auth_account_logs.event', $request->get('event'));
        }
        
        $logs = $query->orderBy('auth_account_logs.created_at', 'desc')->get();
        
        $format = $request->get('format', 'csv');
        
        if ($format === 'json') {
            return $this->exportAsJson($logs, 'account_logs');
        } else {
            return $this->exportAsCsv($logs, 'account_logs');
        }
    }
    
    /**
     * 보안 로그 내보내기
     * GET /admin/auth/logs/security/export
     */
    public function exportSecurityLogs(Request $request)
    {
        $query = DB::table('auth_password_errors')
            ->join('users', 'auth_password_errors.user_id', '=', 'users.id')
            ->select(
                'auth_password_errors.*',
                'users.name as user_name',
                'users.email as user_email'
            );
        
        // 날짜 필터
        if ($request->has('date_from')) {
            $query->whereDate('auth_password_errors.created_at', '>=', $request->get('date_from'));
        }
        
        if ($request->has('date_to')) {
            $query->whereDate('auth_password_errors.created_at', '<=', $request->get('date_to'));
        }
        
        $logs = $query->orderBy('auth_password_errors.created_at', 'desc')->get();
        
        $format = $request->get('format', 'csv');
        
        if ($format === 'json') {
            return $this->exportAsJson($logs, 'security_logs');
        } else {
            return $this->exportAsCsv($logs, 'security_logs');
        }
    }
    
    /**
     * 권한 변경 로그
     * GET /admin/auth/logs/security/permissions
     */
    public function permissionLogs(Request $request)
    {
        $query = DB::table('auth_account_logs')
            ->join('users', 'auth_account_logs.user_id', '=', 'users.id')
            ->whereIn('auth_account_logs.event', [
                'role_changed',
                'permission_granted',
                'permission_revoked',
                'grade_changed',
                'type_changed'
            ])
            ->select(
                'auth_account_logs.*',
                'users.name as user_name',
                'users.email as user_email'
            );
        
        // 검색
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('users.name', 'like', "%{$search}%")
                  ->orWhere('users.email', 'like', "%{$search}%");
            });
        }
        
        // 날짜 필터
        if ($request->has('date_from')) {
            $query->whereDate('auth_account_logs.created_at', '>=', $request->get('date_from'));
        }
        
        if ($request->has('date_to')) {
            $query->whereDate('auth_account_logs.created_at', '<=', $request->get('date_to'));
        }
        
        $logs = $query->orderBy('auth_account_logs.created_at', 'desc')
            ->paginate(20);
        
        // 통계
        $stats = [
            'total_changes' => DB::table('auth_account_logs')
                ->whereIn('event', ['role_changed', 'permission_granted', 'permission_revoked', 'grade_changed', 'type_changed'])
                ->count(),
            'this_month' => DB::table('auth_account_logs')
                ->whereIn('event', ['role_changed', 'permission_granted', 'permission_revoked', 'grade_changed', 'type_changed'])
                ->whereMonth('created_at', now()->month)
                ->count(),
            'by_type' => DB::table('auth_account_logs')
                ->whereIn('event', ['role_changed', 'permission_granted', 'permission_revoked', 'grade_changed', 'type_changed'])
                ->select('event', DB::raw('COUNT(*) as count'))
                ->groupBy('event')
                ->get()
        ];
        
        return view('jiny-auth::admin.logs.permissions', compact('logs', 'stats'));
    }
    
    /**
     * CSV 형식으로 내보내기
     */
    private function exportAsCsv($data, $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}_" . date('YmdHis') . ".csv\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];
        
        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            
            // BOM for UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // 헤더 행
            if (count($data) > 0) {
                $headers = array_keys((array)$data[0]);
                fputcsv($file, $headers);
                
                // 데이터 행
                foreach ($data as $row) {
                    fputcsv($file, (array)$row);
                }
            }
            
            fclose($file);
        };
        
        return Response::stream($callback, 200, $headers);
    }
    
    /**
     * JSON 형식으로 내보내기
     */
    private function exportAsJson($data, $filename)
    {
        $headers = [
            'Content-Type' => 'application/json; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}_" . date('YmdHis') . ".json\"",
        ];
        
        return Response::make(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), 200, $headers);
    }
}