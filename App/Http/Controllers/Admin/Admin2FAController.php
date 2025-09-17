<?php

namespace Jiny\Auth\App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Models\User;

class Admin2FAController extends Controller
{
    /**
     * 2FA 설정 관리 페이지
     * GET /admin/auth/2fa/settings
     */
    public function settings()
    {
        // 현재 2FA 설정 가져오기
        $settings = [
            'enabled' => config('jiny-auth.security.two_factor.enabled', false),
            'enforced' => config('jiny-auth.security.two_factor.enforced', false),
            'grace_period' => config('jiny-auth.security.two_factor.grace_period', 7),
            'recovery_codes_count' => config('jiny-auth.security.two_factor.recovery_codes_count', 8),
            'remember_days' => config('jiny-auth.security.two_factor.remember_days', 30),
        ];
        
        // 통계 정보
        $statistics = [
            'total_users' => User::count(),
            'enabled_users' => User::whereNotNull('two_factor_secret')->where('two_factor_enabled', true)->count(),
            'disabled_users' => User::where(function($query) {
                $query->whereNull('two_factor_secret')
                      ->orWhere('two_factor_enabled', false);
            })->count(),
            'recovery_codes_used' => DB::table('two_factor_recovery_codes')
                ->whereNotNull('used_at')
                ->count(),
        ];
        
        $statistics['enabled_percentage'] = $statistics['total_users'] > 0 
            ? round(($statistics['enabled_users'] / $statistics['total_users']) * 100, 2) 
            : 0;
        
        return view('jiny-auth::admin.2fa.settings', compact('settings', 'statistics'));
    }
    
    /**
     * 2FA 설정 업데이트
     * POST /admin/auth/2fa/settings
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'enabled' => 'required|boolean',
            'enforced' => 'required|boolean',
            'grace_period' => 'required|integer|min:0|max:30',
            'recovery_codes_count' => 'required|integer|min:4|max:20',
            'remember_days' => 'required|integer|min:1|max:365',
        ]);
        
        // 설정을 캐시에 저장 (실제 환경에서는 config 파일에 저장)
        $settings = [
            'enabled' => $request->enabled,
            'enforced' => $request->enforced,
            'grace_period' => $request->grace_period,
            'recovery_codes_count' => $request->recovery_codes_count,
            'remember_days' => $request->remember_days,
        ];
        
        Cache::put('2fa_settings', $settings, now()->addYear());
        
        // 활동 로그 기록
        $this->logActivity(null, 'admin_2fa_settings_updated', '2FA 설정 변경: ' . json_encode($settings), $request);
        
        return redirect()->route('admin.2fa.settings')
            ->with('success', '2FA 설정이 업데이트되었습니다.');
    }
    
    /**
     * 2FA 활성화 사용자 목록
     * GET /admin/auth/2fa/users
     */
    public function users(Request $request)
    {
        $query = User::query();
        
        // 검색 필터
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        // 2FA 상태 필터
        if ($request->has('status')) {
            if ($request->status === 'enabled') {
                $query->whereNotNull('two_factor_secret')
                      ->where('two_factor_enabled', true);
            } elseif ($request->status === 'disabled') {
                $query->where(function($q) {
                    $q->whereNull('two_factor_secret')
                      ->orWhere('two_factor_enabled', false);
                });
            }
        } else {
            // 기본적으로 2FA 활성화된 사용자만 표시
            $query->whereNotNull('two_factor_secret')
                  ->where('two_factor_enabled', true);
        }
        
        $users = $query->orderBy('created_at', 'desc')->paginate(20);
        
        // 각 사용자의 2FA 관련 정보 추가
        foreach ($users as $user) {
            $user->recovery_codes_remaining = DB::table('two_factor_recovery_codes')
                ->where('user_id', $user->id)
                ->whereNull('used_at')
                ->count();
            
            $user->last_2fa_login = DB::table('user_logs')
                ->where('user_id', $user->id)
                ->where('action', 'login_2fa_success')
                ->orderBy('created_at', 'desc')
                ->first();
        }
        
        return view('jiny-auth::admin.2fa.users', compact('users'));
    }
    
    /**
     * 사용자 2FA 비활성화
     * POST /admin/auth/2fa/users/{id}/disable
     */
    public function disableUser(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        if (!$user->two_factor_secret) {
            return response()->json([
                'success' => false,
                'message' => '이미 2FA가 비활성화되어 있습니다.'
            ]);
        }
        
        // 2FA 비활성화
        $user->update([
            'two_factor_secret' => null,
            'two_factor_enabled' => false,
        ]);
        
        // 복구 코드 삭제
        DB::table('two_factor_recovery_codes')
            ->where('user_id', $user->id)
            ->delete();
        
        // 활동 로그 기록
        $this->logActivity($user->id, 'admin_2fa_disabled', '관리자에 의한 2FA 비활성화', $request);
        
        // 사용자에게 알림 (이메일 등)
        $this->notifyUser($user, 'disabled');
        
        return response()->json([
            'success' => true,
            'message' => '사용자의 2FA가 비활성화되었습니다.'
        ]);
    }
    
    /**
     * 사용자 2FA 강제 활성화
     * POST /admin/auth/2fa/users/{id}/force-enable
     */
    public function forceEnableUser(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        if ($user->two_factor_secret && $user->two_factor_enabled) {
            return response()->json([
                'success' => false,
                'message' => '이미 2FA가 활성화되어 있습니다.'
            ]);
        }
        
        // 2FA 강제 활성화 플래그 설정
        $user->update([
            'two_factor_required' => true,
            'two_factor_required_at' => now(),
            'two_factor_required_by' => Auth::id(),
        ]);
        
        // 활동 로그 기록
        $this->logActivity($user->id, 'admin_2fa_force_enabled', '관리자에 의한 2FA 강제 활성화 요청', $request);
        
        // 사용자에게 알림 (이메일 등)
        $this->notifyUser($user, 'required');
        
        return response()->json([
            'success' => true,
            'message' => '사용자에게 2FA 활성화가 요청되었습니다. 다음 로그인 시 설정하도록 안내됩니다.'
        ]);
    }
    
    /**
     * 사용자 2FA 상태 토글
     * POST /admin/auth/2fa/users/{id}/toggle
     */
    public function toggleUser(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        if ($user->two_factor_secret && $user->two_factor_enabled) {
            // 비활성화
            return $this->disableUser($request, $id);
        } else {
            // 강제 활성화 요청
            return $this->forceEnableUser($request, $id);
        }
    }
    
    /**
     * 2FA 통계
     * GET /admin/auth/2fa/statistics
     */
    public function statistics()
    {
        $statistics = [
            // 전체 통계
            'total_users' => User::count(),
            'enabled_users' => User::whereNotNull('two_factor_secret')->where('two_factor_enabled', true)->count(),
            'required_users' => User::where('two_factor_required', true)->whereNull('two_factor_secret')->count(),
            
            // 최근 30일 통계
            'recent_enabled' => User::whereNotNull('two_factor_secret')
                ->where('two_factor_enabled', true)
                ->where('created_at', '>=', now()->subDays(30))
                ->count(),
            
            // 복구 코드 사용 통계
            'total_recovery_codes' => DB::table('two_factor_recovery_codes')->count(),
            'used_recovery_codes' => DB::table('two_factor_recovery_codes')->whereNotNull('used_at')->count(),
            
            // 로그인 통계
            'successful_2fa_logins' => DB::table('user_logs')
                ->where('action', 'login_2fa_success')
                ->where('created_at', '>=', now()->subDays(30))
                ->count(),
            
            'failed_2fa_logins' => DB::table('user_logs')
                ->where('action', 'login_2fa_failed')
                ->where('created_at', '>=', now()->subDays(30))
                ->count(),
        ];
        
        // 일별 통계 (최근 30일)
        $daily_stats = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $daily_stats[$date] = [
                'enabled' => User::whereNotNull('two_factor_secret')
                    ->whereDate('updated_at', $date)
                    ->count(),
                'logins' => DB::table('user_logs')
                    ->where('action', 'login_2fa_success')
                    ->whereDate('created_at', $date)
                    ->count(),
            ];
        }
        
        return response()->json([
            'statistics' => $statistics,
            'daily_stats' => $daily_stats
        ]);
    }
    
    /**
     * 사용자 2FA 상세 정보
     * GET /admin/auth/2fa/users/{id}/details
     */
    public function userDetails($id)
    {
        $user = User::findOrFail($id);
        
        // 2FA 로그인 기록
        $recentLogins = DB::table('user_logs')
            ->where('user_id', $user->id)
            ->whereIn('action', ['login_2fa_success', 'login_2fa_failed'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        // 복구 코드 상태
        $recoveryCodes = DB::table('two_factor_recovery_codes')
            ->where('user_id', $user->id)
            ->get();
        
        return view('jiny-auth::admin.2fa.user-details', compact('user', 'recentLogins', 'recoveryCodes'));
    }
    
    /**
     * 전체 사용자에게 2FA 활성화 요청
     * POST /admin/auth/2fa/request-all
     */
    public function requestAll(Request $request)
    {
        $users = User::whereNull('two_factor_secret')->get();
        
        foreach ($users as $user) {
            $user->update([
                'two_factor_required' => true,
                'two_factor_required_at' => now(),
                'two_factor_required_by' => Auth::id(),
            ]);
            
            // 활동 로그 기록
            $this->logActivity($user->id, 'admin_2fa_bulk_request', '일괄 2FA 활성화 요청', $request);
        }
        
        return response()->json([
            'success' => true,
            'message' => $users->count() . '명의 사용자에게 2FA 활성화가 요청되었습니다.'
        ]);
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
    
    /**
     * 사용자에게 알림 발송
     */
    private function notifyUser($user, $type)
    {
        // 이메일 알림 구현 (Mail facade 사용)
        // 예시: Mail::to($user->email)->send(new TwoFactorStatusChanged($type));
        
        // 인앱 알림 구현 (필요시)
        // 예시: $user->notify(new TwoFactorStatusNotification($type));
    }
}