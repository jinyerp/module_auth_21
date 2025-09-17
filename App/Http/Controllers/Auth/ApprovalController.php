<?php

namespace Jiny\Auth\App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Models\User;

class ApprovalController extends Controller
{
    /**
     * 승인 대기 페이지 표시
     * GET /register/approval
     */
    public function index()
    {
        $user = Auth::user();
        
        // 로그인하지 않은 경우
        if (!$user) {
            return redirect()->route('login')
                ->with('info', '승인 상태를 확인하려면 로그인이 필요합니다.');
        }
        
        // 이미 승인된 경우
        if ($user->approved_at) {
            return redirect()->route('home')
                ->with('success', '계정이 이미 승인되었습니다.');
        }
        
        return view('jiny-auth::auth.approval-waiting', [
            'user' => $user,
            'status' => $user->approval_status ?? 'pending'
        ]);
    }
    
    /**
     * 승인 상태 확인 (AJAX)
     * POST /register/approval/check
     */
    public function check(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => '인증되지 않은 사용자입니다.'
            ], 401);
        }
        
        // 승인 상태 확인
        if ($user->approved_at) {
            return response()->json([
                'success' => true,
                'status' => 'approved',
                'message' => '계정이 승인되었습니다.',
                'redirect' => route('home')
            ]);
        }
        
        if ($user->approval_status === 'rejected') {
            return response()->json([
                'success' => true,
                'status' => 'rejected',
                'message' => '계정 승인이 거부되었습니다.',
                'reason' => $user->rejection_reason
            ]);
        }
        
        return response()->json([
            'success' => true,
            'status' => 'pending',
            'message' => '승인 대기 중입니다.',
            'submitted_at' => $user->created_at->format('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * 승인 요청 재전송
     * POST /register/approval/resend
     */
    public function resend(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => '인증되지 않은 사용자입니다.'
            ], 401);
        }
        
        // 이미 승인된 경우
        if ($user->approved_at) {
            return response()->json([
                'success' => false,
                'message' => '이미 승인된 계정입니다.'
            ]);
        }
        
        // 거부된 계정의 경우 상태 초기화
        if ($user->approval_status === 'rejected') {
            $user->update([
                'approval_status' => 'pending',
                'rejection_reason' => null,
                'rejected_at' => null,
                'rejected_by' => null
            ]);
        }
        
        // 관리자에게 알림 이메일 발송
        $this->sendApprovalNotificationToAdmins($user);
        
        // 활동 로그 기록
        if (\Illuminate\Support\Facades\Schema::hasTable('user_logs')) {
            DB::table('user_logs')->insert([
                'user_id' => $user->id,
                'action' => 'approval_request_resent',
                'description' => '승인 요청 재전송',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now()
            ]);
        }
        
        return response()->json([
            'success' => true,
            'message' => '승인 요청이 재전송되었습니다.'
        ]);
    }
    
    /**
     * 관리자에게 승인 알림 이메일 발송
     */
    private function sendApprovalNotificationToAdmins($user)
    {
        // 관리자 목록 조회
        $admins = User::where('is_admin', true)
            ->orWhere('role', 'admin')
            ->get();
        
        foreach ($admins as $admin) {
            Mail::send('jiny-auth::emails.approval-request', [
                'admin' => $admin,
                'user' => $user,
                'approvalUrl' => route('admin.auth.approval')
            ], function ($message) use ($admin) {
                $message->to($admin->email)
                    ->subject('[승인 요청] 새로운 회원가입 승인 요청');
            });
        }
    }
}