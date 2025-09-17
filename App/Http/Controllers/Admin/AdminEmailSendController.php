<?php

namespace Jiny\Auth\App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Jiny\Auth\App\Services\EmailService;

class AdminEmailSendController extends Controller
{
    protected $emailService;
    
    public function __construct()
    {
        $this->emailService = new EmailService();
    }
    
    /**
     * 이메일 발송 폼
     * GET /admin/auth/emails/send
     */
    public function create(Request $request)
    {
        $templates = DB::table('auth_email_templates')
            ->where('is_active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get();
        
        $users = User::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
        
        return view('jiny-auth::admin.emails.send', compact('templates', 'users'));
    }
    
    /**
     * 이메일 발송
     * POST /admin/auth/emails/send
     */
    public function send(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'recipient_type' => 'required|in:email,user,all,role,group',
            'email' => 'required_if:recipient_type,email|email',
            'user_id' => 'required_if:recipient_type,user|exists:users,id',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'template_name' => 'nullable|exists:auth_email_templates,name',
            'send_immediately' => 'boolean',
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        // 수신자 결정
        $recipients = [];
        
        switch ($request->recipient_type) {
            case 'email':
                $recipients[] = [
                    'email' => $request->email,
                    'name' => null,
                    'user_id' => null,
                ];
                break;
                
            case 'user':
                $user = User::find($request->user_id);
                $recipients[] = [
                    'email' => $user->email,
                    'name' => $user->name,
                    'user_id' => $user->id,
                ];
                break;
                
            case 'all':
                $users = User::where('is_active', true)->get();
                foreach ($users as $user) {
                    // 이메일 수신 동의 확인
                    $settings = DB::table('auth_notification_settings')
                        ->where('user_id', $user->id)
                        ->first();
                    
                    if (!$settings || $settings->email_enabled) {
                        $recipients[] = [
                            'email' => $user->email,
                            'name' => $user->name,
                            'user_id' => $user->id,
                        ];
                    }
                }
                break;
                
            case 'role':
                // 역할별 사용자 (역할 시스템이 구현되면)
                break;
                
            case 'group':
                // 그룹별 사용자 (그룹 시스템이 구현되면)
                break;
        }
        
        if (empty($recipients)) {
            return back()->with('error', '수신자가 없습니다.')->withInput();
        }
        
        // 대량 발송인 경우
        if (count($recipients) > 1) {
            $bulkId = DB::table('auth_bulk_notifications')->insertGetId([
                'type' => 'email',
                'name' => $request->subject,
                'subject' => $request->subject,
                'content' => $request->body,
                'template_name' => $request->template_name,
                'target_type' => $request->recipient_type,
                'target_criteria' => json_encode($request->all()),
                'total_recipients' => count($recipients),
                'status' => 'processing',
                'created_by' => auth()->id(),
                'started_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            $sent = 0;
            $failed = 0;
            
            foreach ($recipients as $recipient) {
                $result = $this->sendEmail($recipient, $request, $bulkId);
                if ($result) {
                    $sent++;
                } else {
                    $failed++;
                }
            }
            
            // 대량 발송 상태 업데이트
            DB::table('auth_bulk_notifications')
                ->where('id', $bulkId)
                ->update([
                    'sent_count' => $sent,
                    'failed_count' => $failed,
                    'status' => 'completed',
                    'completed_at' => now(),
                    'updated_at' => now(),
                ]);
            
            return redirect()->route('admin.auth.emails.logs')
                ->with('success', "{$sent}개의 이메일을 발송했습니다. (실패: {$failed}개)");
        } else {
            // 단일 발송
            $result = $this->sendEmail($recipients[0], $request);
            
            if ($result) {
                return redirect()->route('admin.auth.emails.logs')
                    ->with('success', '이메일을 발송했습니다.');
            } else {
                return back()->with('error', '이메일 발송에 실패했습니다.')->withInput();
            }
        }
    }
    
    /**
     * 이메일 발송 로그
     * GET /admin/auth/emails/logs
     */
    public function logs(Request $request)
    {
        $query = DB::table('auth_email_logs')
            ->leftJoin('users', 'auth_email_logs.user_id', '=', 'users.id')
            ->select(
                'auth_email_logs.*',
                'users.name as user_name'
            );
        
        // 검색
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('auth_email_logs.to', 'like', "%{$search}%")
                  ->orWhere('auth_email_logs.subject', 'like', "%{$search}%")
                  ->orWhere('users.name', 'like', "%{$search}%");
            });
        }
        
        // 필터
        if ($request->has('status')) {
            $query->where('auth_email_logs.status', $request->get('status'));
        }
        
        if ($request->has('template_name')) {
            $query->where('auth_email_logs.template_name', $request->get('template_name'));
        }
        
        if ($request->has('date_from')) {
            $query->whereDate('auth_email_logs.created_at', '>=', $request->get('date_from'));
        }
        
        if ($request->has('date_to')) {
            $query->whereDate('auth_email_logs.created_at', '<=', $request->get('date_to'));
        }
        
        $logs = $query->orderBy('auth_email_logs.created_at', 'desc')
            ->paginate(20);
        
        // 통계
        $stats = [
            'total' => DB::table('auth_email_logs')->count(),
            'sent' => DB::table('auth_email_logs')->where('status', 'sent')->count(),
            'failed' => DB::table('auth_email_logs')->where('status', 'failed')->count(),
            'opened' => DB::table('auth_email_logs')->whereNotNull('opened_at')->count(),
            'clicked' => DB::table('auth_email_logs')->whereNotNull('clicked_at')->count(),
        ];
        
        // 템플릿 목록
        $templates = DB::table('auth_email_templates')
            ->where('is_active', true)
            ->orderBy('name')
            ->pluck('name');
        
        return view('jiny-auth::admin.emails.logs', compact('logs', 'stats', 'templates'));
    }
    
    /**
     * 이메일 재발송
     * POST /admin/auth/emails/logs/{id}/resend
     */
    public function resend(Request $request, $id)
    {
        $log = DB::table('auth_email_logs')->where('id', $id)->first();
        
        if (!$log) {
            return response()->json([
                'success' => false,
                'message' => '이메일 로그를 찾을 수 없습니다.'
            ], 404);
        }
        
        // 재발송
        $recipient = [
            'email' => $log->to,
            'name' => null,
            'user_id' => $log->user_id,
        ];
        
        $emailRequest = (object)[
            'subject' => $log->subject,
            'body' => $log->body,
            'template_name' => $log->template_name,
        ];
        
        $result = $this->sendEmail($recipient, $emailRequest);
        
        if ($result) {
            return response()->json([
                'success' => true,
                'message' => '이메일을 재발송했습니다.'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => '이메일 재발송에 실패했습니다.'
            ], 500);
        }
    }
    
    /**
     * 이메일 로그 상세
     * GET /admin/auth/emails/logs/{id}
     */
    public function show(Request $request, $id)
    {
        $log = DB::table('auth_email_logs')
            ->leftJoin('users', 'auth_email_logs.user_id', '=', 'users.id')
            ->select(
                'auth_email_logs.*',
                'users.name as user_name',
                'users.email as user_email'
            )
            ->where('auth_email_logs.id', $id)
            ->first();
        
        if (!$log) {
            return redirect()->route('admin.auth.emails.logs')
                ->with('error', '이메일 로그를 찾을 수 없습니다.');
        }
        
        $log->headers = json_decode($log->headers, true) ?? [];
        $log->attachments = json_decode($log->attachments, true) ?? [];
        $log->metadata = json_decode($log->metadata, true) ?? [];
        
        return view('jiny-auth::admin.emails.log-detail', compact('log'));
    }
    
    /**
     * 대량 발송 목록
     * GET /admin/auth/emails/bulk
     */
    public function bulkList(Request $request)
    {
        $bulkJobs = DB::table('auth_bulk_notifications')
            ->where('type', 'email')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('jiny-auth::admin.emails.bulk', compact('bulkJobs'));
    }
    
    /**
     * 실제 이메일 발송
     */
    private function sendEmail($recipient, $request, $bulkId = null)
    {
        try {
            // 이메일 로그 생성
            $logId = DB::table('auth_email_logs')->insertGetId([
                'user_id' => $recipient['user_id'],
                'to' => $recipient['email'],
                'from' => config('mail.from.address'),
                'subject' => $request->subject,
                'body' => $request->body,
                'template_name' => $request->template_name,
                'status' => 'pending',
                'metadata' => json_encode(['bulk_id' => $bulkId]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // 템플릿 사용 횟수 증가
            if ($request->template_name) {
                DB::table('auth_email_templates')
                    ->where('name', $request->template_name)
                    ->increment('usage_count');
            }
            
            // 이메일 발송 (EmailService 사용)
            $result = $this->emailService->send(
                $recipient['email'],
                $request->subject,
                $request->body,
                $recipient['name']
            );
            
            // 로그 업데이트
            DB::table('auth_email_logs')->where('id', $logId)->update([
                'status' => $result ? 'sent' : 'failed',
                'sent_at' => $result ? now() : null,
                'error_message' => $result ? null : '발송 실패',
                'updated_at' => now(),
            ]);
            
            return $result;
        } catch (\Exception $e) {
            // 오류 로깅
            if (isset($logId)) {
                DB::table('auth_email_logs')->where('id', $logId)->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'updated_at' => now(),
                ]);
            }
            
            return false;
        }
    }
}