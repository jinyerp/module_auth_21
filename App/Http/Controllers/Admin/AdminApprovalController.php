<?php

namespace Jiny\Auth\App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AdminApprovalController extends Controller
{
    /**
     * 승인 대기 목록
     * GET /admin/auth/approval
     */
    public function index()
    {
        $pendingUsers = User::where('approval_status', 'pending')
            ->orWhereNull('approved_at')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        $rejectedUsers = User::where('approval_status', 'rejected')
            ->orderBy('rejected_at', 'desc')
            ->paginate(20);
        
        $statistics = [
            'pending' => User::where('approval_status', 'pending')->count(),
            'approved' => User::whereNotNull('approved_at')->count(),
            'rejected' => User::where('approval_status', 'rejected')->count(),
        ];
        
        return view('jiny-auth::admin.approval.index', compact('pendingUsers', 'rejectedUsers', 'statistics'));
    }
    
    /**
     * 개별 승인
     * POST /admin/auth/approval/{id}/approve
     */
    public function approve(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        // 이미 승인된 경우
        if ($user->approved_at) {
            return response()->json([
                'success' => false,
                'message' => '이미 승인된 사용자입니다.'
            ]);
        }
        
        // 승인 처리
        $user->update([
            'approval_status' => 'approved',
            'approved_at' => now(),
            'approved_by' => Auth::id(),
            'rejection_reason' => null,
            'rejected_at' => null,
            'rejected_by' => null
        ]);
        
        // 사용자에게 승인 알림 이메일 발송
        $this->sendApprovalNotification($user);
        
        // 활동 로그 기록
        $this->logActivity($user->id, 'user_approved', '회원가입 승인', $request);
        
        return response()->json([
            'success' => true,
            'message' => '사용자가 승인되었습니다.'
        ]);
    }
    
    /**
     * 개별 거부
     * POST /admin/auth/approval/{id}/reject
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);
        
        $user = User::findOrFail($id);
        
        // 이미 처리된 경우
        if ($user->approved_at) {
            return response()->json([
                'success' => false,
                'message' => '이미 승인된 사용자입니다.'
            ]);
        }
        
        // 거부 처리
        $user->update([
            'approval_status' => 'rejected',
            'rejection_reason' => $request->reason,
            'rejected_at' => now(),
            'rejected_by' => Auth::id(),
            'approved_at' => null,
            'approved_by' => null
        ]);
        
        // 사용자에게 거부 알림 이메일 발송
        $this->sendRejectionNotification($user, $request->reason);
        
        // 활동 로그 기록
        $this->logActivity($user->id, 'user_rejected', '회원가입 거부: ' . $request->reason, $request);
        
        return response()->json([
            'success' => true,
            'message' => '사용자가 거부되었습니다.'
        ]);
    }
    
    /**
     * 일괄 승인
     * POST /admin/auth/approval/bulk-approve
     */
    public function bulkApprove(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id'
        ]);
        
        $count = 0;
        foreach ($request->user_ids as $userId) {
            $user = User::find($userId);
            
            if ($user && !$user->approved_at) {
                $user->update([
                    'approval_status' => 'approved',
                    'approved_at' => now(),
                    'approved_by' => Auth::id(),
                    'rejection_reason' => null,
                    'rejected_at' => null,
                    'rejected_by' => null
                ]);
                
                $this->sendApprovalNotification($user);
                $this->logActivity($user->id, 'user_approved', '일괄 회원가입 승인', $request);
                $count++;
            }
        }
        
        return response()->json([
            'success' => true,
            'message' => $count . '명의 사용자가 승인되었습니다.'
        ]);
    }
    
    /**
     * 일괄 거부
     * POST /admin/auth/approval/bulk-reject
     */
    public function bulkReject(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'reason' => 'required|string|max:500'
        ]);
        
        $count = 0;
        foreach ($request->user_ids as $userId) {
            $user = User::find($userId);
            
            if ($user && !$user->approved_at) {
                $user->update([
                    'approval_status' => 'rejected',
                    'rejection_reason' => $request->reason,
                    'rejected_at' => now(),
                    'rejected_by' => Auth::id(),
                    'approved_at' => null,
                    'approved_by' => null
                ]);
                
                $this->sendRejectionNotification($user, $request->reason);
                $this->logActivity($user->id, 'user_rejected', '일괄 회원가입 거부: ' . $request->reason, $request);
                $count++;
            }
        }
        
        return response()->json([
            'success' => true,
            'message' => $count . '명의 사용자가 거부되었습니다.'
        ]);
    }
    
    /**
     * 승인 알림 이메일 발송
     */
    private function sendApprovalNotification($user)
    {
        Mail::send('jiny-auth::emails.approval-approved', [
            'user' => $user,
            'loginUrl' => route('login')
        ], function ($message) use ($user) {
            $message->to($user->email)
                ->subject('[승인 완료] 회원가입이 승인되었습니다');
        });
    }
    
    /**
     * 거부 알림 이메일 발송
     */
    private function sendRejectionNotification($user, $reason)
    {
        Mail::send('jiny-auth::emails.approval-rejected', [
            'user' => $user,
            'reason' => $reason,
            'contactUrl' => config('app.url') . '/contact'
        ], function ($message) use ($user) {
            $message->to($user->email)
                ->subject('[승인 거부] 회원가입이 거부되었습니다');
        });
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
     * 사용자 상세 정보 조회
     * GET /admin/auth/approval/{id}
     */
    public function show($id)
    {
        $user = User::findOrFail($id);
        
        // 사용자의 추가 정보 조회
        $additionalInfo = [
            'login_count' => DB::table('user_logs')
                ->where('user_id', $id)
                ->where('action', 'login')
                ->count(),
            'last_activity' => DB::table('user_logs')
                ->where('user_id', $id)
                ->orderBy('created_at', 'desc')
                ->first(),
            'ip_addresses' => DB::table('user_logs')
                ->where('user_id', $id)
                ->distinct()
                ->pluck('ip_address')
        ];
        
        return view('jiny-auth::admin.approval.show', compact('user', 'additionalInfo'));
    }
}