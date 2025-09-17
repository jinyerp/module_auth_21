<?php

namespace Jiny\Auth\App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * 비밀번호 정책 관리 컨트롤러
 * 관리자용 비밀번호 정책 설정 및 관리
 */
class PasswordPolicyController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * 비밀번호 정책 설정 페이지
     * GET /admin/auth/passwords/policy
     */
    public function index()
    {
        // 현재 정책 로드
        $policy = DB::table('password_policies')->first() ?? $this->getDefaultPolicy();
        
        return view('jiny-auth::admin.password-policy', compact('policy'));
    }

    /**
     * 비밀번호 정책 업데이트
     * POST /admin/auth/passwords/policy
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'min_length' => 'required|integer|min:6|max:32',
            'require_uppercase' => 'boolean',
            'require_lowercase' => 'boolean',
            'require_numbers' => 'boolean',
            'require_special' => 'boolean',
            'expiry_days' => 'nullable|integer|min:0|max:365',
            'history_count' => 'nullable|integer|min:0|max:10',
            'max_attempts' => 'required|integer|min:3|max:10',
            'lockout_duration' => 'required|integer|min:5|max:1440', // minutes
        ]);

        // 정책 업데이트 또는 생성
        DB::table('password_policies')->updateOrInsert(
            ['id' => 1],
            array_merge($validated, [
                'updated_at' => now(),
                'updated_by' => auth()->id(),
            ])
        );

        // 활동 로그 기록
        DB::table('account_logs')->insert([
            'account_id' => auth()->id(),
            'action' => 'password_policy_update',
            'description' => '비밀번호 정책 변경',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now()
        ]);

        return redirect()->route('admin.auth.passwords.policy')
            ->with('success', '비밀번호 정책이 업데이트되었습니다.');
    }

    /**
     * 만료된 비밀번호 목록
     * GET /admin/auth/passwords/expired
     */
    public function expired()
    {
        $policy = DB::table('password_policies')->first();
        
        if (!$policy || !$policy->expiry_days) {
            $expiredUsers = collect();
        } else {
            $expiryDate = Carbon::now()->subDays($policy->expiry_days);
            
            $expiredUsers = DB::table('accounts')
                ->where(function($query) use ($expiryDate) {
                    $query->where('password_changed_at', '<', $expiryDate)
                          ->orWhereNull('password_changed_at');
                })
                ->where('status', 'active')
                ->select('id', 'name', 'email', 'password_changed_at')
                ->paginate(20);
        }

        return view('jiny-auth::admin.password-expired', compact('expiredUsers', 'policy'));
    }

    /**
     * 비밀번호 강제 변경 설정
     * POST /admin/auth/passwords/force-change
     */
    public function forceChange(Request $request)
    {
        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:accounts,id',
            'reason' => 'nullable|string|max:255',
        ]);

        // 선택된 사용자들의 비밀번호 강제 변경 플래그 설정
        DB::table('accounts')
            ->whereIn('id', $validated['user_ids'])
            ->update([
                'password_force_change' => true,
                'password_force_change_reason' => $validated['reason'] ?? '관리자 요청',
                'updated_at' => now(),
            ]);

        // 각 사용자에 대한 로그 기록
        foreach ($validated['user_ids'] as $userId) {
            DB::table('account_logs')->insert([
                'account_id' => $userId,
                'action' => 'password_force_change_set',
                'description' => '비밀번호 강제 변경 설정: ' . ($validated['reason'] ?? '관리자 요청'),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now()
            ]);
        }

        return back()->with('success', count($validated['user_ids']) . '명의 사용자에게 비밀번호 강제 변경이 설정되었습니다.');
    }

    /**
     * 비밀번호 정책 통계
     * GET /admin/auth/passwords/statistics
     */
    public function statistics()
    {
        $stats = [
            'total_users' => DB::table('accounts')->count(),
            'expired_passwords' => $this->getExpiredPasswordCount(),
            'force_change_pending' => DB::table('accounts')
                ->where('password_force_change', true)
                ->count(),
            'weak_passwords' => $this->getWeakPasswordCount(),
            'recent_changes' => DB::table('password_histories')
                ->where('created_at', '>=', Carbon::now()->subDays(30))
                ->count(),
        ];

        return view('jiny-auth::admin.password-statistics', compact('stats'));
    }

    /**
     * 기본 정책 반환
     */
    private function getDefaultPolicy()
    {
        return (object) [
            'min_length' => 8,
            'require_uppercase' => true,
            'require_lowercase' => true,
            'require_numbers' => true,
            'require_special' => false,
            'expiry_days' => 90,
            'history_count' => 3,
            'max_attempts' => 5,
            'lockout_duration' => 15,
        ];
    }

    /**
     * 만료된 비밀번호 수 계산
     */
    private function getExpiredPasswordCount()
    {
        $policy = DB::table('password_policies')->first();
        
        if (!$policy || !$policy->expiry_days) {
            return 0;
        }

        $expiryDate = Carbon::now()->subDays($policy->expiry_days);
        
        return DB::table('accounts')
            ->where(function($query) use ($expiryDate) {
                $query->where('password_changed_at', '<', $expiryDate)
                      ->orWhereNull('password_changed_at');
            })
            ->where('status', 'active')
            ->count();
    }

    /**
     * 약한 비밀번호 수 계산 (추정치)
     */
    private function getWeakPasswordCount()
    {
        // 실제로는 비밀번호를 검사할 수 없으므로, 
        // 최근 정책 변경 이전에 설정된 비밀번호 수를 반환
        $policy = DB::table('password_policies')->first();
        
        if (!$policy || !$policy->updated_at) {
            return 0;
        }

        return DB::table('accounts')
            ->where('password_changed_at', '<', $policy->updated_at)
            ->where('status', 'active')
            ->count();
    }
}