<?php

namespace Jiny\Auth\App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Jiny\Auth\App\Models\Account;

/**
 * 계정 상태 관리 컨트롤러
 * 관리자용 사용자 계정 상태 변경
 */
class AccountStatusController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * 계정 활성화
     * POST /admin/auth/users/{id}/activate
     */
    public function activate(Request $request, $id)
    {
        $account = Account::findOrFail($id);
        
        if ($account->status === 'active') {
            return back()->with('info', '이미 활성화된 계정입니다.');
        }

        $account->update([
            'status' => 'active',
            'activated_at' => now(),
            'activated_by' => auth()->id(),
        ]);

        // 활동 로그 기록
        $this->logStatusChange($account, 'activate', '계정 활성화');

        return back()->with('success', '계정이 활성화되었습니다.');
    }

    /**
     * 계정 비활성화
     * POST /admin/auth/users/{id}/deactivate
     */
    public function deactivate(Request $request, $id)
    {
        $account = Account::findOrFail($id);
        
        if ($account->status === 'inactive') {
            return back()->with('info', '이미 비활성화된 계정입니다.');
        }

        $reason = $request->input('reason', '관리자 요청');

        $account->update([
            'status' => 'inactive',
            'deactivated_at' => now(),
            'deactivated_by' => auth()->id(),
            'deactivation_reason' => $reason,
        ]);

        // 모든 세션 종료
        DB::table('sessions')->where('user_id', $id)->delete();
        
        // JWT 토큰 무효화
        DB::table('jwt_tokens')
            ->where('account_id', $id)
            ->whereNull('invalidated_at')
            ->update(['invalidated_at' => now()]);

        // 활동 로그 기록
        $this->logStatusChange($account, 'deactivate', '계정 비활성화: ' . $reason);

        return back()->with('success', '계정이 비활성화되었습니다.');
    }

    /**
     * 계정 정지
     * POST /admin/auth/users/{id}/suspend
     */
    public function suspend(Request $request, $id)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:255',
            'duration' => 'nullable|integer|min:1|max:365', // days
            'permanent' => 'boolean',
        ]);

        $account = Account::findOrFail($id);
        
        if ($account->status === 'suspended') {
            return back()->with('info', '이미 정지된 계정입니다.');
        }

        $suspendedUntil = null;
        if (!$validated['permanent'] && isset($validated['duration'])) {
            $suspendedUntil = now()->addDays($validated['duration']);
        }

        $account->update([
            'status' => 'suspended',
            'suspended_at' => now(),
            'suspended_until' => $suspendedUntil,
            'suspended_by' => auth()->id(),
            'suspension_reason' => $validated['reason'],
        ]);

        // 모든 세션 종료
        DB::table('sessions')->where('user_id', $id)->delete();
        
        // JWT 토큰 무효화
        DB::table('jwt_tokens')
            ->where('account_id', $id)
            ->whereNull('invalidated_at')
            ->update(['invalidated_at' => now()]);

        // 활동 로그 기록
        $description = '계정 정지: ' . $validated['reason'];
        if ($suspendedUntil) {
            $description .= ' (기간: ' . $validated['duration'] . '일)';
        } else {
            $description .= ' (영구 정지)';
        }
        $this->logStatusChange($account, 'suspend', $description);

        // 이메일 알림 발송 (선택적)
        // Mail::to($account->email)->send(new AccountSuspended($account, $validated['reason']));

        return back()->with('success', '계정이 정지되었습니다.');
    }

    /**
     * 계정 정지 해제
     * POST /admin/auth/users/{id}/unsuspend
     */
    public function unsuspend(Request $request, $id)
    {
        $account = Account::findOrFail($id);
        
        if ($account->status !== 'suspended') {
            return back()->with('info', '정지되지 않은 계정입니다.');
        }

        $reason = $request->input('reason', '관리자 요청');

        $account->update([
            'status' => 'active',
            'suspended_at' => null,
            'suspended_until' => null,
            'suspended_by' => null,
            'suspension_reason' => null,
            'unsuspended_at' => now(),
            'unsuspended_by' => auth()->id(),
        ]);

        // 활동 로그 기록
        $this->logStatusChange($account, 'unsuspend', '계정 정지 해제: ' . $reason);

        return back()->with('success', '계정 정지가 해제되었습니다.');
    }

    /**
     * 일괄 상태 변경
     * POST /admin/auth/users/bulk-status
     */
    public function bulkStatusChange(Request $request)
    {
        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:accounts,id',
            'action' => 'required|in:activate,deactivate,suspend,unsuspend',
            'reason' => 'nullable|string|max:255',
            'duration' => 'nullable|integer|min:1|max:365', // for suspension
        ]);

        $successCount = 0;
        $failCount = 0;

        foreach ($validated['user_ids'] as $userId) {
            try {
                switch ($validated['action']) {
                    case 'activate':
                        $this->performActivate($userId);
                        break;
                    case 'deactivate':
                        $this->performDeactivate($userId, $validated['reason'] ?? '일괄 처리');
                        break;
                    case 'suspend':
                        $this->performSuspend($userId, $validated['reason'] ?? '일괄 처리', $validated['duration'] ?? null);
                        break;
                    case 'unsuspend':
                        $this->performUnsuspend($userId, $validated['reason'] ?? '일괄 처리');
                        break;
                }
                $successCount++;
            } catch (\Exception $e) {
                $failCount++;
            }
        }

        $message = "{$successCount}개 계정이 처리되었습니다.";
        if ($failCount > 0) {
            $message .= " ({$failCount}개 실패)";
        }

        return back()->with('success', $message);
    }

    /**
     * 상태 변경 로그 기록
     */
    private function logStatusChange($account, $action, $description)
    {
        DB::table('account_logs')->insert([
            'account_id' => $account->id,
            'action' => 'status_' . $action,
            'description' => $description,
            'target_id' => $account->id,
            'target_type' => 'account',
            'performed_by' => auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now()
        ]);

        // 관리자 로그도 기록
        DB::table('admin_logs')->insert([
            'admin_id' => auth()->id(),
            'action' => 'account_status_change',
            'target_type' => 'account',
            'target_id' => $account->id,
            'description' => $description,
            'ip_address' => request()->ip(),
            'created_at' => now()
        ]);
    }

    /**
     * 활성화 수행
     */
    private function performActivate($userId)
    {
        $account = Account::findOrFail($userId);
        
        if ($account->status !== 'active') {
            $account->update([
                'status' => 'active',
                'activated_at' => now(),
                'activated_by' => auth()->id(),
            ]);
            
            $this->logStatusChange($account, 'activate', '일괄 계정 활성화');
        }
    }

    /**
     * 비활성화 수행
     */
    private function performDeactivate($userId, $reason)
    {
        $account = Account::findOrFail($userId);
        
        if ($account->status !== 'inactive') {
            $account->update([
                'status' => 'inactive',
                'deactivated_at' => now(),
                'deactivated_by' => auth()->id(),
                'deactivation_reason' => $reason,
            ]);
            
            DB::table('sessions')->where('user_id', $userId)->delete();
            DB::table('jwt_tokens')
                ->where('account_id', $userId)
                ->whereNull('invalidated_at')
                ->update(['invalidated_at' => now()]);
            
            $this->logStatusChange($account, 'deactivate', '일괄 계정 비활성화: ' . $reason);
        }
    }

    /**
     * 정지 수행
     */
    private function performSuspend($userId, $reason, $duration = null)
    {
        $account = Account::findOrFail($userId);
        
        if ($account->status !== 'suspended') {
            $suspendedUntil = $duration ? now()->addDays($duration) : null;
            
            $account->update([
                'status' => 'suspended',
                'suspended_at' => now(),
                'suspended_until' => $suspendedUntil,
                'suspended_by' => auth()->id(),
                'suspension_reason' => $reason,
            ]);
            
            DB::table('sessions')->where('user_id', $userId)->delete();
            DB::table('jwt_tokens')
                ->where('account_id', $userId)
                ->whereNull('invalidated_at')
                ->update(['invalidated_at' => now()]);
            
            $description = '일괄 계정 정지: ' . $reason;
            if ($duration) {
                $description .= ' (기간: ' . $duration . '일)';
            }
            $this->logStatusChange($account, 'suspend', $description);
        }
    }

    /**
     * 정지 해제 수행
     */
    private function performUnsuspend($userId, $reason)
    {
        $account = Account::findOrFail($userId);
        
        if ($account->status === 'suspended') {
            $account->update([
                'status' => 'active',
                'suspended_at' => null,
                'suspended_until' => null,
                'suspended_by' => null,
                'suspension_reason' => null,
                'unsuspended_at' => now(),
                'unsuspended_by' => auth()->id(),
            ]);
            
            $this->logStatusChange($account, 'unsuspend', '일괄 계정 정지 해제: ' . $reason);
        }
    }
}