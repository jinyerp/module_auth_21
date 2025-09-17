<?php

namespace Jiny\Auth\App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\User;
use Carbon\Carbon;

class AdminDormantController extends Controller
{
    /**
     * 휴면계정 목록
     * GET /admin/auth/users/dormant
     */
    public function index(Request $request)
    {
        $query = User::where('is_dormant', true);
        
        // 검색 필터
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        // 휴면 기간 필터
        if ($request->has('dormant_period')) {
            if ($request->dormant_period === '30days') {
                $query->where('dormant_at', '>=', now()->subDays(30));
            } elseif ($request->dormant_period === '90days') {
                $query->where('dormant_at', '>=', now()->subDays(90));
            } elseif ($request->dormant_period === '1year') {
                $query->where('dormant_at', '>=', now()->subYear());
            } elseif ($request->dormant_period === 'over1year') {
                $query->where('dormant_at', '<', now()->subYear());
            }
        }
        
        // 삭제 예정 필터
        if ($request->has('scheduled_delete')) {
            if ($request->scheduled_delete === 'yes') {
                $query->whereNotNull('dormant_scheduled_delete_at');
            } elseif ($request->scheduled_delete === 'no') {
                $query->whereNull('dormant_scheduled_delete_at');
            }
        }
        
        $dormantUsers = $query->orderBy('dormant_at', 'desc')->paginate(20);
        
        // 각 사용자의 휴면 정보 가공
        foreach ($dormantUsers as $user) {
            $user->dormant_days = Carbon::parse($user->dormant_at)->diffInDays(now());
            $user->dormant_at_formatted = Carbon::parse($user->dormant_at)->format('Y-m-d H:i');
            
            if ($user->dormant_scheduled_delete_at) {
                $user->days_until_delete = now()->diffInDays(Carbon::parse($user->dormant_scheduled_delete_at), false);
                $user->scheduled_delete_formatted = Carbon::parse($user->dormant_scheduled_delete_at)->format('Y-m-d');
            }
        }
        
        // 통계 정보
        $statistics = $this->getStatistics();
        
        return view('jiny-auth::admin.dormant.index', compact('dormantUsers', 'statistics'));
    }
    
    /**
     * 휴면계정 통계
     * GET /admin/auth/users/dormant/statistics
     */
    public function statistics(Request $request)
    {
        $statistics = $this->getStatistics();
        
        // 월별 휴면 전환 추이 (최근 12개월)
        $monthlyTrends = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthlyTrends[$month->format('Y-m')] = [
                'marked_dormant' => DB::table('dormant_logs')
                    ->where('action', 'marked_dormant')
                    ->whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->count(),
                'activated' => DB::table('dormant_logs')
                    ->where('action', 'activated')
                    ->whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->count(),
                'deleted' => DB::table('dormant_logs')
                    ->where('action', 'deleted')
                    ->whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->count(),
            ];
        }
        
        // 휴면 기간별 분포
        $dormantDistribution = [
            '0-30days' => User::where('is_dormant', true)
                ->where('dormant_at', '>=', now()->subDays(30))
                ->count(),
            '31-90days' => User::where('is_dormant', true)
                ->where('dormant_at', '<', now()->subDays(30))
                ->where('dormant_at', '>=', now()->subDays(90))
                ->count(),
            '91-365days' => User::where('is_dormant', true)
                ->where('dormant_at', '<', now()->subDays(90))
                ->where('dormant_at', '>=', now()->subYear())
                ->count(),
            'over365days' => User::where('is_dormant', true)
                ->where('dormant_at', '<', now()->subYear())
                ->count(),
        ];
        
        // 삭제 예정 계정
        $scheduledDeletes = User::where('is_dormant', true)
            ->whereNotNull('dormant_scheduled_delete_at')
            ->orderBy('dormant_scheduled_delete_at')
            ->limit(20)
            ->get();
        
        foreach ($scheduledDeletes as $user) {
            $user->days_until_delete = now()->diffInDays(Carbon::parse($user->dormant_scheduled_delete_at), false);
        }
        
        return view('jiny-auth::admin.dormant.statistics', compact(
            'statistics', 
            'monthlyTrends', 
            'dormantDistribution',
            'scheduledDeletes'
        ));
    }
    
    /**
     * 휴면계정 활성화
     * POST /admin/auth/users/dormant/{id}/activate
     */
    public function activate(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        if (!$user->is_dormant) {
            return response()->json([
                'success' => false,
                'message' => '이미 활성 계정입니다.'
            ]);
        }
        
        // 휴면계정 활성화
        $user->update([
            'is_dormant' => false,
            'dormant_at' => null,
            'dormant_notified_at' => null,
            'dormant_notification_count' => 0,
            'dormant_scheduled_delete_at' => null,
            'dormant_reason' => null,
            'last_activity_at' => now()
        ]);
        
        // 로그 기록
        $this->logActivity($user->id, 'admin_activated', '관리자에 의한 휴면계정 활성화', $request);
        
        return response()->json([
            'success' => true,
            'message' => '휴면계정이 활성화되었습니다.'
        ]);
    }
    
    /**
     * 휴면계정 삭제
     * POST /admin/auth/users/dormant/{id}/delete
     */
    public function delete(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        if (!$user->is_dormant) {
            return response()->json([
                'success' => false,
                'message' => '휴면계정이 아닙니다.'
            ]);
        }
        
        // 로그 기록 (삭제 전)
        $this->logActivity($user->id, 'admin_deleted', '관리자에 의한 휴면계정 삭제', $request);
        
        // 사용자 삭제 (소프트 삭제 또는 하드 삭제)
        $user->delete();
        
        return response()->json([
            'success' => true,
            'message' => '휴면계정이 삭제되었습니다.'
        ]);
    }
    
    /**
     * 일괄 활성화
     * POST /admin/auth/users/dormant/bulk-activate
     */
    public function bulkActivate(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'integer|exists:users,id'
        ]);
        
        $users = User::whereIn('id', $request->user_ids)
            ->where('is_dormant', true)
            ->get();
        
        $activatedCount = 0;
        
        foreach ($users as $user) {
            $user->update([
                'is_dormant' => false,
                'dormant_at' => null,
                'dormant_notified_at' => null,
                'dormant_notification_count' => 0,
                'dormant_scheduled_delete_at' => null,
                'dormant_reason' => null,
                'last_activity_at' => now()
            ]);
            
            $this->logActivity($user->id, 'admin_bulk_activated', '관리자에 의한 일괄 휴면계정 활성화', $request);
            $activatedCount++;
        }
        
        return response()->json([
            'success' => true,
            'message' => "{$activatedCount}개의 휴면계정이 활성화되었습니다."
        ]);
    }
    
    /**
     * 일괄 삭제
     * POST /admin/auth/users/dormant/bulk-delete
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'integer|exists:users,id'
        ]);
        
        $users = User::whereIn('id', $request->user_ids)
            ->where('is_dormant', true)
            ->get();
        
        $deletedCount = 0;
        
        foreach ($users as $user) {
            $this->logActivity($user->id, 'admin_bulk_deleted', '관리자에 의한 일괄 휴면계정 삭제', $request);
            $user->delete();
            $deletedCount++;
        }
        
        return response()->json([
            'success' => true,
            'message' => "{$deletedCount}개의 휴면계정이 삭제되었습니다."
        ]);
    }
    
    /**
     * 휴면계정 정책 설정
     * GET /admin/auth/users/dormant/settings
     */
    public function settings(Request $request)
    {
        // 현재 설정 가져오기
        $settings = [
            'enabled' => Cache::get('dormant_enabled', true),
            'inactive_days' => Cache::get('dormant_inactive_days', 365),
            'warning_days' => Cache::get('dormant_warning_days', 30),
            'notification_count' => Cache::get('dormant_notification_count', 3),
            'delete_after_days' => Cache::get('dormant_delete_after_days', 90),
            'auto_delete' => Cache::get('dormant_auto_delete', false),
            'exclude_admins' => Cache::get('dormant_exclude_admins', true),
        ];
        
        return view('jiny-auth::admin.dormant.settings', compact('settings'));
    }
    
    /**
     * 휴면계정 정책 업데이트
     * POST /admin/auth/users/dormant/settings
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'enabled' => 'required|boolean',
            'inactive_days' => 'required|integer|min:30|max:730',
            'warning_days' => 'required|integer|min:7|max:90',
            'notification_count' => 'required|integer|min:1|max:10',
            'delete_after_days' => 'required|integer|min:30|max:365',
            'auto_delete' => 'required|boolean',
            'exclude_admins' => 'required|boolean',
        ]);
        
        // 설정 저장 (캐시에 저장, 실제 환경에서는 config 파일에 저장)
        Cache::put('dormant_enabled', $request->enabled, now()->addYear());
        Cache::put('dormant_inactive_days', $request->inactive_days, now()->addYear());
        Cache::put('dormant_warning_days', $request->warning_days, now()->addYear());
        Cache::put('dormant_notification_count', $request->notification_count, now()->addYear());
        Cache::put('dormant_delete_after_days', $request->delete_after_days, now()->addYear());
        Cache::put('dormant_auto_delete', $request->auto_delete, now()->addYear());
        Cache::put('dormant_exclude_admins', $request->exclude_admins, now()->addYear());
        
        // 로그 기록
        DB::table('dormant_logs')->insert([
            'user_id' => null,
            'action' => 'settings_updated',
            'description' => '휴면계정 정책 설정 변경',
            'admin_id' => Auth::id(),
            'metadata' => json_encode($request->all()),
            'created_at' => now()
        ]);
        
        return redirect()->route('admin.auth.users.dormant.settings')
            ->with('success', '휴면계정 정책이 업데이트되었습니다.');
    }
    
    /**
     * 통계 정보 가져오기
     */
    private function getStatistics()
    {
        return [
            'total_dormant' => User::where('is_dormant', true)->count(),
            'total_active' => User::where('is_dormant', false)->count(),
            'recent_dormant' => User::where('is_dormant', true)
                ->where('dormant_at', '>=', now()->subDays(30))
                ->count(),
            'scheduled_delete' => User::where('is_dormant', true)
                ->whereNotNull('dormant_scheduled_delete_at')
                ->count(),
            'delete_within_7days' => User::where('is_dormant', true)
                ->whereNotNull('dormant_scheduled_delete_at')
                ->where('dormant_scheduled_delete_at', '<=', now()->addDays(7))
                ->count(),
            'activated_this_month' => DB::table('dormant_logs')
                ->where('action', 'activated')
                ->where('created_at', '>=', now()->startOfMonth())
                ->count(),
            'deleted_this_month' => DB::table('dormant_logs')
                ->where('action', 'deleted')
                ->where('created_at', '>=', now()->startOfMonth())
                ->count(),
        ];
    }
    
    /**
     * 활동 로그 기록
     */
    private function logActivity($userId, $action, $description, $request)
    {
        DB::table('dormant_logs')->insert([
            'user_id' => $userId,
            'action' => $action,
            'description' => $description,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'admin_id' => Auth::id(),
            'created_at' => now()
        ]);
    }
}