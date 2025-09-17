<?php

namespace Jiny\Auth\App\Http\Controllers\Home;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class MessageController extends Controller
{
    /**
     * 메시지 목록
     * GET /home/message
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $type = $request->get('type', 'inbox'); // inbox, sent, starred, archived
        
        $query = DB::table('user_messages')
            ->join('users as sender', 'user_messages.sender_id', '=', 'sender.id', 'left')
            ->join('users as recipient', 'user_messages.recipient_id', '=', 'recipient.id')
            ->select(
                'user_messages.*',
                'sender.name as sender_name',
                'sender.email as sender_email',
                'recipient.name as recipient_name',
                'recipient.email as recipient_email'
            );
        
        switch ($type) {
            case 'sent':
                $query->where('user_messages.sender_id', $user->id)
                      ->where('user_messages.sender_deleted', false);
                break;
            case 'starred':
                $query->where('user_messages.recipient_id', $user->id)
                      ->where('user_messages.is_starred', true)
                      ->where('user_messages.recipient_deleted', false);
                break;
            case 'archived':
                $query->where('user_messages.recipient_id', $user->id)
                      ->where('user_messages.is_archived', true)
                      ->where('user_messages.recipient_deleted', false);
                break;
            default: // inbox
                $query->where('user_messages.recipient_id', $user->id)
                      ->where('user_messages.is_archived', false)
                      ->where('user_messages.recipient_deleted', false);
                break;
        }
        
        // 검색
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('user_messages.subject', 'like', "%{$search}%")
                  ->orWhere('user_messages.content', 'like', "%{$search}%")
                  ->orWhere('sender.name', 'like', "%{$search}%")
                  ->orWhere('sender.email', 'like', "%{$search}%");
            });
        }
        
        // 필터
        if ($request->has('priority')) {
            $query->where('user_messages.priority', $request->get('priority'));
        }
        
        if ($request->has('is_read')) {
            $query->where('user_messages.is_read', $request->get('is_read') === 'true');
        }
        
        $messages = $query->orderBy('user_messages.created_at', 'desc')
            ->paginate(20);
        
        // 읽지 않은 메시지 수
        $unreadCount = DB::table('user_messages')
            ->where('recipient_id', $user->id)
            ->where('is_read', false)
            ->where('is_archived', false)
            ->where('recipient_deleted', false)
            ->count();
        
        return view('jiny-auth::home.messages.index', compact('messages', 'type', 'unreadCount'));
    }
    
    /**
     * 메시지 작성 폼
     * GET /home/message/compose
     */
    public function compose(Request $request)
    {
        $recipient = null;
        if ($request->has('to')) {
            $recipient = User::find($request->get('to'));
        }
        
        // 메시지 템플릿
        $templates = DB::table('message_templates')
            ->where('is_active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get();
        
        return view('jiny-auth::home.messages.compose', compact('recipient', 'templates'));
    }
    
    /**
     * 메시지 발송
     * POST /home/message
     */
    public function send(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'recipient_email' => 'required|email|exists:users,email',
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'priority' => 'in:low,normal,high,urgent',
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        $sender = Auth::user();
        $recipient = User::where('email', $request->recipient_email)->first();
        
        // 자기 자신에게 보낼 수 없음
        if ($sender->id === $recipient->id) {
            return back()->with('error', '자기 자신에게는 메시지를 보낼 수 없습니다.')->withInput();
        }
        
        // 차단 확인
        $isBlocked = DB::table('message_blocks')
            ->where('user_id', $recipient->id)
            ->where('blocked_user_id', $sender->id)
            ->exists();
        
        if ($isBlocked) {
            return back()->with('error', '해당 사용자가 메시지 수신을 차단했습니다.')->withInput();
        }
        
        // 메시지 저장
        $messageId = DB::table('user_messages')->insertGetId([
            'sender_id' => $sender->id,
            'recipient_id' => $recipient->id,
            'type' => 'message',
            'subject' => $request->subject,
            'content' => $request->content,
            'priority' => $request->priority ?? 'normal',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // 알림 발송 (이메일, 푸시 등)
        $this->sendNotification($recipient, $messageId);
        
        return redirect()->route('home.message')
            ->with('success', '메시지가 성공적으로 발송되었습니다.');
    }
    
    /**
     * 메시지 상세 조회
     * GET /home/message/{id}
     */
    public function show(Request $request, $id)
    {
        $user = Auth::user();
        
        $message = DB::table('user_messages')
            ->join('users as sender', 'user_messages.sender_id', '=', 'sender.id', 'left')
            ->join('users as recipient', 'user_messages.recipient_id', '=', 'recipient.id')
            ->select(
                'user_messages.*',
                'sender.name as sender_name',
                'sender.email as sender_email',
                'sender.avatar as sender_avatar',
                'recipient.name as recipient_name',
                'recipient.email as recipient_email'
            )
            ->where('user_messages.id', $id)
            ->where(function ($query) use ($user) {
                $query->where(function ($q) use ($user) {
                    $q->where('user_messages.recipient_id', $user->id)
                      ->where('user_messages.recipient_deleted', false);
                })->orWhere(function ($q) use ($user) {
                    $q->where('user_messages.sender_id', $user->id)
                      ->where('user_messages.sender_deleted', false);
                });
            })
            ->first();
        
        if (!$message) {
            return redirect()->route('home.message')
                ->with('error', '메시지를 찾을 수 없습니다.');
        }
        
        // 받은 메시지인 경우 읽음 처리
        if ($message->recipient_id == $user->id && !$message->is_read) {
            DB::table('user_messages')
                ->where('id', $id)
                ->update([
                    'is_read' => true,
                    'read_at' => now(),
                    'updated_at' => now(),
                ]);
        }
        
        return view('jiny-auth::home.messages.show', compact('message'));
    }
    
    /**
     * 메시지 읽음 처리
     * POST /home/message/{id}/read
     */
    public function markAsRead(Request $request, $id)
    {
        $user = Auth::user();
        
        $updated = DB::table('user_messages')
            ->where('id', $id)
            ->where('recipient_id', $user->id)
            ->update([
                'is_read' => true,
                'read_at' => now(),
                'updated_at' => now(),
            ]);
        
        if (!$updated) {
            return response()->json([
                'success' => false,
                'message' => '메시지를 찾을 수 없습니다.'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'message' => '읽음 처리되었습니다.'
        ]);
    }
    
    /**
     * 메시지 별표 토글
     * POST /home/message/{id}/star
     */
    public function toggleStar(Request $request, $id)
    {
        $user = Auth::user();
        
        $message = DB::table('user_messages')
            ->where('id', $id)
            ->where('recipient_id', $user->id)
            ->first();
        
        if (!$message) {
            return response()->json([
                'success' => false,
                'message' => '메시지를 찾을 수 없습니다.'
            ], 404);
        }
        
        DB::table('user_messages')
            ->where('id', $id)
            ->update([
                'is_starred' => !$message->is_starred,
                'updated_at' => now(),
            ]);
        
        return response()->json([
            'success' => true,
            'is_starred' => !$message->is_starred
        ]);
    }
    
    /**
     * 메시지 보관
     * POST /home/message/{id}/archive
     */
    public function archive(Request $request, $id)
    {
        $user = Auth::user();
        
        $updated = DB::table('user_messages')
            ->where('id', $id)
            ->where('recipient_id', $user->id)
            ->update([
                'is_archived' => true,
                'updated_at' => now(),
            ]);
        
        if (!$updated) {
            return response()->json([
                'success' => false,
                'message' => '메시지를 찾을 수 없습니다.'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'message' => '메시지가 보관되었습니다.'
        ]);
    }
    
    /**
     * 메시지 삭제
     * DELETE /home/message/{id}
     */
    public function destroy(Request $request, $id)
    {
        $user = Auth::user();
        
        $message = DB::table('user_messages')
            ->where('id', $id)
            ->where(function ($query) use ($user) {
                $query->where('recipient_id', $user->id)
                      ->orWhere('sender_id', $user->id);
            })
            ->first();
        
        if (!$message) {
            return response()->json([
                'success' => false,
                'message' => '메시지를 찾을 수 없습니다.'
            ], 404);
        }
        
        // 발신자/수신자별로 삭제 플래그 설정
        if ($message->sender_id == $user->id) {
            DB::table('user_messages')
                ->where('id', $id)
                ->update(['sender_deleted' => true]);
        }
        
        if ($message->recipient_id == $user->id) {
            DB::table('user_messages')
                ->where('id', $id)
                ->update(['recipient_deleted' => true]);
        }
        
        return response()->json([
            'success' => true,
            'message' => '메시지가 삭제되었습니다.'
        ]);
    }
    
    /**
     * 사용자 차단
     * POST /home/message/block
     */
    public function blockUser(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'reason' => 'nullable|string|max:255'
        ]);
        
        $user = Auth::user();
        
        // 자기 자신을 차단할 수 없음
        if ($user->id == $request->user_id) {
            return response()->json([
                'success' => false,
                'message' => '자기 자신을 차단할 수 없습니다.'
            ], 400);
        }
        
        // 이미 차단했는지 확인
        $exists = DB::table('message_blocks')
            ->where('user_id', $user->id)
            ->where('blocked_user_id', $request->user_id)
            ->exists();
        
        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => '이미 차단된 사용자입니다.'
            ], 400);
        }
        
        DB::table('message_blocks')->insert([
            'user_id' => $user->id,
            'blocked_user_id' => $request->user_id,
            'reason' => $request->reason,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        return response()->json([
            'success' => true,
            'message' => '사용자를 차단했습니다.'
        ]);
    }
    
    /**
     * 차단 해제
     * DELETE /home/message/block/{userId}
     */
    public function unblockUser(Request $request, $userId)
    {
        $user = Auth::user();
        
        $deleted = DB::table('message_blocks')
            ->where('user_id', $user->id)
            ->where('blocked_user_id', $userId)
            ->delete();
        
        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => '차단된 사용자가 아닙니다.'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'message' => '차단이 해제되었습니다.'
        ]);
    }
    
    /**
     * 차단 목록
     * GET /home/message/blocked
     */
    public function blockedUsers(Request $request)
    {
        $user = Auth::user();
        
        $blockedUsers = DB::table('message_blocks')
            ->join('users', 'message_blocks.blocked_user_id', '=', 'users.id')
            ->where('message_blocks.user_id', $user->id)
            ->select(
                'message_blocks.*',
                'users.name',
                'users.email',
                'users.avatar'
            )
            ->orderBy('message_blocks.created_at', 'desc')
            ->paginate(20);
        
        return view('jiny-auth::home.messages.blocked', compact('blockedUsers'));
    }
    
    /**
     * 알림 설정
     * GET /home/message/settings
     */
    public function settings(Request $request)
    {
        $user = Auth::user();
        
        $settings = DB::table('message_notifications')
            ->where('user_id', $user->id)
            ->first();
        
        if (!$settings) {
            // 기본 설정 생성
            DB::table('message_notifications')->insert([
                'user_id' => $user->id,
                'email_enabled' => true,
                'push_enabled' => true,
                'sms_enabled' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            $settings = DB::table('message_notifications')
                ->where('user_id', $user->id)
                ->first();
        }
        
        return view('jiny-auth::home.messages.settings', compact('settings'));
    }
    
    /**
     * 알림 설정 업데이트
     * POST /home/message/settings
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'email_enabled' => 'boolean',
            'push_enabled' => 'boolean',
            'sms_enabled' => 'boolean',
        ]);
        
        $user = Auth::user();
        
        DB::table('message_notifications')
            ->updateOrInsert(
                ['user_id' => $user->id],
                [
                    'email_enabled' => $request->email_enabled ?? true,
                    'push_enabled' => $request->push_enabled ?? true,
                    'sms_enabled' => $request->sms_enabled ?? false,
                    'updated_at' => now(),
                ]
            );
        
        return redirect()->route('home.message.settings')
            ->with('success', '알림 설정이 업데이트되었습니다.');
    }
    
    /**
     * 알림 발송
     */
    private function sendNotification($recipient, $messageId)
    {
        $settings = DB::table('message_notifications')
            ->where('user_id', $recipient->id)
            ->first();
        
        if (!$settings) {
            $settings = (object)[
                'email_enabled' => true,
                'push_enabled' => true,
                'sms_enabled' => false
            ];
        }
        
        $message = DB::table('user_messages')
            ->join('users as sender', 'user_messages.sender_id', '=', 'sender.id', 'left')
            ->select('user_messages.*', 'sender.name as sender_name')
            ->where('user_messages.id', $messageId)
            ->first();
        
        // 이메일 알림
        if ($settings->email_enabled) {
            // TODO: 이메일 발송 로직
        }
        
        // 푸시 알림
        if ($settings->push_enabled) {
            // TODO: 푸시 알림 로직
        }
        
        // SMS 알림
        if ($settings->sms_enabled) {
            // TODO: SMS 발송 로직
        }
    }
}