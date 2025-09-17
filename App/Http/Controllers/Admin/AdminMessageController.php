<?php

namespace Jiny\Auth\App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class AdminMessageController extends Controller
{
    /**
     * 메시지 관리 대시보드
     * GET /admin/auth/message
     */
    public function index(Request $request)
    {
        // 전체 통계
        $stats = [
            'total_messages' => DB::table('user_messages')->count(),
            'today_messages' => DB::table('user_messages')
                ->whereDate('created_at', today())
                ->count(),
            'unread_messages' => DB::table('user_messages')
                ->where('is_read', false)
                ->count(),
            'system_messages' => DB::table('user_messages')
                ->where('type', 'system')
                ->count(),
            'blocked_users' => DB::table('message_blocks')->count(),
            'active_threads' => DB::table('message_threads')
                ->where('last_message_at', '>=', now()->subDays(7))
                ->count(),
        ];
        
        // 최근 메시지
        $recentMessages = DB::table('user_messages')
            ->join('users as sender', 'user_messages.sender_id', '=', 'sender.id', 'left')
            ->join('users as recipient', 'user_messages.recipient_id', '=', 'recipient.id')
            ->select(
                'user_messages.*',
                'sender.name as sender_name',
                'sender.email as sender_email',
                'recipient.name as recipient_name',
                'recipient.email as recipient_email'
            )
            ->orderBy('user_messages.created_at', 'desc')
            ->limit(10)
            ->get();
        
        // 대량 발송 현황
        $bulkMessages = DB::table('bulk_messages')
            ->join('users', 'bulk_messages.sender_id', '=', 'users.id')
            ->select(
                'bulk_messages.*',
                'users.name as sender_name'
            )
            ->orderBy('bulk_messages.created_at', 'desc')
            ->limit(5)
            ->get();
        
        return view('jiny-auth::admin.messages.index', compact('stats', 'recentMessages', 'bulkMessages'));
    }
    
    /**
     * 메시지 상세 조회
     * GET /admin/auth/message/{id}
     */
    public function show(Request $request, $id)
    {
        $message = DB::table('user_messages')
            ->join('users as sender', 'user_messages.sender_id', '=', 'sender.id', 'left')
            ->join('users as recipient', 'user_messages.recipient_id', '=', 'recipient.id')
            ->select(
                'user_messages.*',
                'sender.name as sender_name',
                'sender.email as sender_email',
                'recipient.name as recipient_name',
                'recipient.email as recipient_email'
            )
            ->where('user_messages.id', $id)
            ->first();
        
        if (!$message) {
            return redirect()->route('admin.auth.message')
                ->with('error', '메시지를 찾을 수 없습니다.');
        }
        
        return view('jiny-auth::admin.messages.show', compact('message'));
    }
    
    /**
     * 시스템 메시지 발송 폼
     * GET /admin/auth/message/compose
     */
    public function compose(Request $request)
    {
        $templates = DB::table('message_templates')
            ->where('is_active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get();
        
        $users = User::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
        
        return view('jiny-auth::admin.messages.compose', compact('templates', 'users'));
    }
    
    /**
     * 관리자 메시지 발송
     * POST /admin/auth/message
     */
    public function send(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'recipient_type' => 'required|in:user,all,role,group',
            'recipient_id' => 'required_if:recipient_type,user',
            'role' => 'required_if:recipient_type,role',
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:message,system,notification,announcement',
            'priority' => 'required|in:low,normal,high,urgent',
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        $admin = Auth::user();
        
        // 대상 사용자 결정
        $recipients = [];
        
        switch ($request->recipient_type) {
            case 'user':
                $recipients = [$request->recipient_id];
                break;
                
            case 'all':
                $recipients = User::where('is_active', true)->pluck('id')->toArray();
                break;
                
            case 'role':
                // 역할별 사용자 (role 시스템이 있다면)
                // $recipients = User::role($request->role)->pluck('id')->toArray();
                break;
                
            case 'group':
                // 그룹별 사용자 (그룹 시스템이 있다면)
                break;
        }
        
        if (count($recipients) > 1) {
            // 대량 발송
            $bulkId = DB::table('bulk_messages')->insertGetId([
                'sender_id' => $admin->id,
                'subject' => $request->subject,
                'content' => $request->content,
                'target_type' => $request->recipient_type,
                'target_criteria' => json_encode($request->all()),
                'total_recipients' => count($recipients),
                'status' => 'processing',
                'started_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            $sent = 0;
            $failed = 0;
            
            foreach ($recipients as $recipientId) {
                try {
                    DB::table('user_messages')->insert([
                        'sender_id' => null, // 시스템 메시지
                        'recipient_id' => $recipientId,
                        'type' => $request->type,
                        'subject' => $request->subject,
                        'content' => $request->content,
                        'priority' => $request->priority,
                        'metadata' => json_encode(['bulk_id' => $bulkId]),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $sent++;
                } catch (\Exception $e) {
                    $failed++;
                }
            }
            
            // 대량 발송 상태 업데이트
            DB::table('bulk_messages')
                ->where('id', $bulkId)
                ->update([
                    'sent_count' => $sent,
                    'failed_count' => $failed,
                    'status' => 'completed',
                    'completed_at' => now(),
                    'updated_at' => now(),
                ]);
            
            return redirect()->route('admin.auth.message')
                ->with('success', "{$sent}명에게 메시지를 발송했습니다. (실패: {$failed}명)");
        } else {
            // 단일 발송
            DB::table('user_messages')->insert([
                'sender_id' => null, // 시스템 메시지
                'recipient_id' => $recipients[0],
                'type' => $request->type,
                'subject' => $request->subject,
                'content' => $request->content,
                'priority' => $request->priority,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            return redirect()->route('admin.auth.message')
                ->with('success', '메시지를 발송했습니다.');
        }
    }
    
    /**
     * 메시지 템플릿 관리
     * GET /admin/auth/message/templates
     */
    public function templates(Request $request)
    {
        $templates = DB::table('message_templates')
            ->orderBy('category')
            ->orderBy('name')
            ->paginate(20);
        
        return view('jiny-auth::admin.messages.templates', compact('templates'));
    }
    
    /**
     * 템플릿 생성 폼
     * GET /admin/auth/message/templates/create
     */
    public function createTemplate(Request $request)
    {
        return view('jiny-auth::admin.messages.template-create');
    }
    
    /**
     * 템플릿 저장
     * POST /admin/auth/message/templates
     */
    public function storeTemplate(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:50',
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
        ]);
        
        DB::table('message_templates')->insert([
            'name' => $request->name,
            'category' => $request->category,
            'subject' => $request->subject,
            'content' => $request->content,
            'variables' => json_encode($request->variables ?? []),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        return redirect()->route('admin.auth.message.templates')
            ->with('success', '템플릿이 생성되었습니다.');
    }
    
    /**
     * 템플릿 수정 폼
     * GET /admin/auth/message/templates/{id}/edit
     */
    public function editTemplate(Request $request, $id)
    {
        $template = DB::table('message_templates')->where('id', $id)->first();
        
        if (!$template) {
            return redirect()->route('admin.auth.message.templates')
                ->with('error', '템플릿을 찾을 수 없습니다.');
        }
        
        return view('jiny-auth::admin.messages.template-edit', compact('template'));
    }
    
    /**
     * 템플릿 업데이트
     * PUT /admin/auth/message/templates/{id}
     */
    public function updateTemplate(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:50',
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'is_active' => 'boolean',
        ]);
        
        DB::table('message_templates')
            ->where('id', $id)
            ->update([
                'name' => $request->name,
                'category' => $request->category,
                'subject' => $request->subject,
                'content' => $request->content,
                'variables' => json_encode($request->variables ?? []),
                'is_active' => $request->is_active ?? true,
                'updated_at' => now(),
            ]);
        
        return redirect()->route('admin.auth.message.templates')
            ->with('success', '템플릿이 업데이트되었습니다.');
    }
    
    /**
     * 템플릿 삭제
     * DELETE /admin/auth/message/templates/{id}
     */
    public function deleteTemplate(Request $request, $id)
    {
        DB::table('message_templates')->where('id', $id)->delete();
        
        return response()->json([
            'success' => true,
            'message' => '템플릿이 삭제되었습니다.'
        ]);
    }
    
    /**
     * 차단 사용자 관리
     * GET /admin/auth/message/blocked
     */
    public function blockedUsers(Request $request)
    {
        $blockedUsers = DB::table('message_blocks')
            ->join('users as blocker', 'message_blocks.user_id', '=', 'blocker.id')
            ->join('users as blocked', 'message_blocks.blocked_user_id', '=', 'blocked.id')
            ->select(
                'message_blocks.*',
                'blocker.name as blocker_name',
                'blocker.email as blocker_email',
                'blocked.name as blocked_name',
                'blocked.email as blocked_email'
            )
            ->orderBy('message_blocks.created_at', 'desc')
            ->paginate(20);
        
        return view('jiny-auth::admin.messages.blocked', compact('blockedUsers'));
    }
    
    /**
     * 차단 해제
     * DELETE /admin/auth/message/blocked/{id}
     */
    public function unblock(Request $request, $id)
    {
        DB::table('message_blocks')->where('id', $id)->delete();
        
        return response()->json([
            'success' => true,
            'message' => '차단이 해제되었습니다.'
        ]);
    }
    
    /**
     * 메시지 통계
     * GET /admin/auth/message/statistics
     */
    public function statistics(Request $request)
    {
        $period = $request->get('period', '30'); // 기본 30일
        
        // 일별 메시지 발송 추이
        $dailyMessages = DB::table('user_messages')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(CASE WHEN is_read = 1 THEN 1 ELSE 0 END) as read_count')
            )
            ->where('created_at', '>=', now()->subDays($period))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        // 메시지 타입별 통계
        $typeStats = DB::table('user_messages')
            ->select('type', DB::raw('COUNT(*) as count'))
            ->where('created_at', '>=', now()->subDays($period))
            ->groupBy('type')
            ->get();
        
        // 우선순위별 통계
        $priorityStats = DB::table('user_messages')
            ->select('priority', DB::raw('COUNT(*) as count'))
            ->where('created_at', '>=', now()->subDays($period))
            ->groupBy('priority')
            ->get();
        
        // 가장 활발한 사용자 (발신)
        $topSenders = DB::table('user_messages')
            ->join('users', 'user_messages.sender_id', '=', 'users.id')
            ->select(
                'users.id',
                'users.name',
                'users.email',
                DB::raw('COUNT(*) as message_count')
            )
            ->whereNotNull('sender_id')
            ->where('user_messages.created_at', '>=', now()->subDays($period))
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderByDesc('message_count')
            ->limit(10)
            ->get();
        
        // 가장 활발한 사용자 (수신)
        $topRecipients = DB::table('user_messages')
            ->join('users', 'user_messages.recipient_id', '=', 'users.id')
            ->select(
                'users.id',
                'users.name',
                'users.email',
                DB::raw('COUNT(*) as message_count')
            )
            ->where('user_messages.created_at', '>=', now()->subDays($period))
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderByDesc('message_count')
            ->limit(10)
            ->get();
        
        // 읽음률
        $readRate = DB::table('user_messages')
            ->where('created_at', '>=', now()->subDays($period))
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN is_read = 1 THEN 1 ELSE 0 END) as read_count
            ')
            ->first();
        
        return view('jiny-auth::admin.messages.statistics', compact(
            'dailyMessages',
            'typeStats',
            'priorityStats',
            'topSenders',
            'topRecipients',
            'readRate',
            'period'
        ));
    }
    
    /**
     * SSE 메시지 테스트
     * GET /admin/auth/message/sse
     */
    public function sseTest(Request $request)
    {
        return view('jiny-auth::admin.messages.sse-test');
    }
    
    /**
     * SSE 메시지 스트림
     * GET /admin/auth/message/sse/stream
     */
    public function sseStream(Request $request)
    {
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('X-Accel-Buffering: no');
        
        $lastId = $request->get('lastEventId', 0);
        
        while (true) {
            // 새 메시지 확인
            $messages = DB::table('user_messages')
                ->where('id', '>', $lastId)
                ->orderBy('id')
                ->limit(10)
                ->get();
            
            foreach ($messages as $message) {
                echo "id: {$message->id}\n";
                echo "data: " . json_encode($message) . "\n\n";
                $lastId = $message->id;
            }
            
            ob_flush();
            flush();
            
            sleep(3); // 3초마다 확인
            
            // 연결 종료 확인
            if (connection_aborted()) {
                break;
            }
        }
    }
}