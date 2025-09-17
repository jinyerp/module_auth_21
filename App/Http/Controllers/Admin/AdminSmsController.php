<?php

namespace Jiny\Auth\App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Jiny\Auth\App\Services\SmsService;

class AdminSmsController extends Controller
{
    protected $smsService;
    
    public function __construct()
    {
        $this->smsService = new SmsService();
    }
    
    /**
     * SMS 발송 폼
     * GET /admin/auth/sms/send
     */
    public function create(Request $request)
    {
        $templates = DB::table('auth_sms_templates')
            ->where('is_active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get();
        
        $senders = DB::table('auth_sms_senders')
            ->where('is_active', true)
            ->orderBy('is_default', 'desc')
            ->orderBy('number')
            ->get();
        
        $users = User::where('is_active', true)
            ->whereNotNull('phone')
            ->orderBy('name')
            ->get(['id', 'name', 'phone']);
        
        return view('jiny-auth::admin.sms.send', compact('templates', 'senders', 'users'));
    }
    
    /**
     * SMS 발송
     * POST /admin/auth/sms/send
     */
    public function send(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'recipient_type' => 'required|in:phone,user,all,role,group',
            'phone' => 'required_if:recipient_type,phone|regex:/^[0-9\-]+$/',
            'user_id' => 'required_if:recipient_type,user|exists:users,id',
            'sender' => 'nullable|exists:auth_sms_senders,number',
            'content' => 'required|string|max:2000',
            'template_name' => 'nullable|exists:auth_sms_templates,name',
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        // 발신번호 결정
        $sender = $request->sender;
        if (!$sender) {
            $defaultSender = DB::table('auth_sms_senders')
                ->where('is_default', true)
                ->where('is_active', true)
                ->first();
            
            if ($defaultSender) {
                $sender = $defaultSender->number;
            }
        }
        
        // 수신자 결정
        $recipients = [];
        
        switch ($request->recipient_type) {
            case 'phone':
                $recipients[] = [
                    'phone' => $this->formatPhoneNumber($request->phone),
                    'name' => null,
                    'user_id' => null,
                ];
                break;
                
            case 'user':
                $user = User::find($request->user_id);
                if ($user->phone) {
                    $recipients[] = [
                        'phone' => $this->formatPhoneNumber($user->phone),
                        'name' => $user->name,
                        'user_id' => $user->id,
                    ];
                }
                break;
                
            case 'all':
                $users = User::where('is_active', true)
                    ->whereNotNull('phone')
                    ->get();
                    
                foreach ($users as $user) {
                    // SMS 수신 동의 확인
                    $settings = DB::table('auth_notification_settings')
                        ->where('user_id', $user->id)
                        ->first();
                    
                    if (!$settings || $settings->sms_enabled) {
                        $recipients[] = [
                            'phone' => $this->formatPhoneNumber($user->phone),
                            'name' => $user->name,
                            'user_id' => $user->id,
                        ];
                    }
                }
                break;
        }
        
        if (empty($recipients)) {
            return back()->with('error', '수신자가 없습니다.')->withInput();
        }
        
        // 대량 발송인 경우
        if (count($recipients) > 1) {
            $bulkId = DB::table('auth_bulk_notifications')->insertGetId([
                'type' => 'sms',
                'name' => 'SMS 대량 발송',
                'content' => $request->content,
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
                $result = $this->sendSms($recipient, $request->content, $sender, $request->template_name, $bulkId);
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
            
            return redirect()->route('admin.auth.sms.logs')
                ->with('success', "{$sent}개의 SMS를 발송했습니다. (실패: {$failed}개)");
        } else {
            // 단일 발송
            $result = $this->sendSms($recipients[0], $request->content, $sender, $request->template_name);
            
            if ($result) {
                return redirect()->route('admin.auth.sms.logs')
                    ->with('success', 'SMS를 발송했습니다.');
            } else {
                return back()->with('error', 'SMS 발송에 실패했습니다.')->withInput();
            }
        }
    }
    
    /**
     * SMS 발송 로그
     * GET /admin/auth/sms/logs
     */
    public function logs(Request $request)
    {
        $query = DB::table('auth_sms_logs')
            ->leftJoin('users', 'auth_sms_logs.user_id', '=', 'users.id')
            ->select(
                'auth_sms_logs.*',
                'users.name as user_name'
            );
        
        // 검색
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('auth_sms_logs.to', 'like', "%{$search}%")
                  ->orWhere('auth_sms_logs.content', 'like', "%{$search}%")
                  ->orWhere('users.name', 'like', "%{$search}%");
            });
        }
        
        // 필터
        if ($request->has('status')) {
            $query->where('auth_sms_logs.status', $request->get('status'));
        }
        
        if ($request->has('provider')) {
            $query->where('auth_sms_logs.provider', $request->get('provider'));
        }
        
        if ($request->has('date_from')) {
            $query->whereDate('auth_sms_logs.created_at', '>=', $request->get('date_from'));
        }
        
        if ($request->has('date_to')) {
            $query->whereDate('auth_sms_logs.created_at', '<=', $request->get('date_to'));
        }
        
        $logs = $query->orderBy('auth_sms_logs.created_at', 'desc')
            ->paginate(20);
        
        // 통계
        $stats = [
            'total' => DB::table('auth_sms_logs')->count(),
            'sent' => DB::table('auth_sms_logs')->where('status', 'sent')->count(),
            'delivered' => DB::table('auth_sms_logs')->where('status', 'delivered')->count(),
            'failed' => DB::table('auth_sms_logs')->where('status', 'failed')->count(),
            'total_cost' => DB::table('auth_sms_logs')->sum('cost'),
        ];
        
        return view('jiny-auth::admin.sms.logs', compact('logs', 'stats'));
    }
    
    /**
     * SMS 템플릿 관리
     * GET /admin/auth/sms/templates
     */
    public function templates(Request $request)
    {
        $query = DB::table('auth_sms_templates');
        
        // 검색
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }
        
        $templates = $query->orderBy('category')
            ->orderBy('name')
            ->paginate(20);
        
        return view('jiny-auth::admin.sms.templates', compact('templates'));
    }
    
    /**
     * SMS 템플릿 생성 폼
     * GET /admin/auth/sms/templates/create
     */
    public function createTemplate(Request $request)
    {
        $categories = DB::table('auth_sms_templates')
            ->select('category')
            ->distinct()
            ->whereNotNull('category')
            ->pluck('category');
        
        $senders = DB::table('auth_sms_senders')
            ->where('is_active', true)
            ->get();
        
        return view('jiny-auth::admin.sms.template-create', compact('categories', 'senders'));
    }
    
    /**
     * SMS 템플릿 저장
     * POST /admin/auth/sms/templates
     */
    public function storeTemplate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:auth_sms_templates,name',
            'title' => 'nullable|string|max:255',
            'content' => 'required|string|max:2000',
            'category' => 'nullable|string|max:50',
            'sender' => 'nullable|exists:auth_sms_senders,number',
            'is_active' => 'boolean',
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        // 변수 추출
        $variables = [];
        if (preg_match_all('/\{\{\s*(\w+)\s*\}\}/', $request->content, $matches)) {
            $variables = array_unique($matches[1]);
        }
        
        DB::table('auth_sms_templates')->insert([
            'name' => $request->name,
            'title' => $request->title,
            'content' => $request->content,
            'category' => $request->category,
            'variables' => json_encode($variables),
            'sender' => $request->sender,
            'is_active' => $request->get('is_active', true),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        return redirect()->route('admin.auth.sms.templates')
            ->with('success', 'SMS 템플릿이 생성되었습니다.');
    }
    
    /**
     * SMS 템플릿 수정 폼
     * GET /admin/auth/sms/templates/{id}/edit
     */
    public function editTemplate(Request $request, $id)
    {
        $template = DB::table('auth_sms_templates')->where('id', $id)->first();
        
        if (!$template) {
            return redirect()->route('admin.auth.sms.templates')
                ->with('error', '템플릿을 찾을 수 없습니다.');
        }
        
        $template->variables = json_decode($template->variables, true) ?? [];
        
        $categories = DB::table('auth_sms_templates')
            ->select('category')
            ->distinct()
            ->whereNotNull('category')
            ->pluck('category');
        
        $senders = DB::table('auth_sms_senders')
            ->where('is_active', true)
            ->get();
        
        return view('jiny-auth::admin.sms.template-edit', compact('template', 'categories', 'senders'));
    }
    
    /**
     * SMS 템플릿 수정
     * PUT /admin/auth/sms/templates/{id}
     */
    public function updateTemplate(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:auth_sms_templates,name,' . $id,
            'title' => 'nullable|string|max:255',
            'content' => 'required|string|max:2000',
            'category' => 'nullable|string|max:50',
            'sender' => 'nullable|exists:auth_sms_senders,number',
            'is_active' => 'boolean',
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        // 변수 추출
        $variables = [];
        if (preg_match_all('/\{\{\s*(\w+)\s*\}\}/', $request->content, $matches)) {
            $variables = array_unique($matches[1]);
        }
        
        DB::table('auth_sms_templates')->where('id', $id)->update([
            'name' => $request->name,
            'title' => $request->title,
            'content' => $request->content,
            'category' => $request->category,
            'variables' => json_encode($variables),
            'sender' => $request->sender,
            'is_active' => $request->get('is_active', true),
            'updated_at' => now(),
        ]);
        
        return redirect()->route('admin.auth.sms.templates')
            ->with('success', 'SMS 템플릿이 수정되었습니다.');
    }
    
    /**
     * SMS 템플릿 삭제
     * DELETE /admin/auth/sms/templates/{id}
     */
    public function destroyTemplate(Request $request, $id)
    {
        DB::table('auth_sms_templates')->where('id', $id)->delete();
        
        return response()->json([
            'success' => true,
            'message' => '템플릿이 삭제되었습니다.'
        ]);
    }
    
    /**
     * 실제 SMS 발송
     */
    private function sendSms($recipient, $content, $sender, $templateName = null, $bulkId = null)
    {
        try {
            // SMS 로그 생성
            $logId = DB::table('auth_sms_logs')->insertGetId([
                'user_id' => $recipient['user_id'],
                'to' => $recipient['phone'],
                'from' => $sender,
                'content' => $content,
                'template_name' => $templateName,
                'provider' => config('sms.default', 'twilio'),
                'status' => 'pending',
                'metadata' => json_encode(['bulk_id' => $bulkId]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // 템플릿 사용 횟수 증가
            if ($templateName) {
                DB::table('auth_sms_templates')
                    ->where('name', $templateName)
                    ->increment('usage_count');
            }
            
            // SMS 발송 (SmsService 사용)
            $result = $this->smsService->send(
                $recipient['phone'],
                $content,
                $sender
            );
            
            // 로그 업데이트
            DB::table('auth_sms_logs')->where('id', $logId)->update([
                'status' => $result['success'] ? 'sent' : 'failed',
                'sent_at' => $result['success'] ? now() : null,
                'message_id' => $result['message_id'] ?? null,
                'cost' => $result['cost'] ?? null,
                'error_message' => $result['error'] ?? null,
                'response' => json_encode($result),
                'updated_at' => now(),
            ]);
            
            return $result['success'];
        } catch (\Exception $e) {
            // 오류 로깅
            if (isset($logId)) {
                DB::table('auth_sms_logs')->where('id', $logId)->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'updated_at' => now(),
                ]);
            }
            
            return false;
        }
    }
    
    /**
     * 전화번호 포맷팅
     */
    private function formatPhoneNumber($phone)
    {
        // 특수문자 제거
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // 국가 코드 추가 (한국)
        if (substr($phone, 0, 1) !== '0') {
            return $phone;
        }
        
        return '82' . substr($phone, 1);
    }
}