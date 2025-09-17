<?php

namespace Jiny\Auth\App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AdminEmailTemplateController extends Controller
{
    /**
     * 이메일 템플릿 목록
     * GET /admin/auth/emails/templates
     */
    public function index(Request $request)
    {
        $query = DB::table('auth_email_templates');
        
        // 검색
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }
        
        // 필터
        if ($request->has('category')) {
            $query->where('category', $request->get('category'));
        }
        
        if ($request->has('is_active')) {
            $query->where('is_active', $request->get('is_active') === 'true');
        }
        
        if ($request->has('locale')) {
            $query->where('locale', $request->get('locale'));
        }
        
        $templates = $query->orderBy('category')
            ->orderBy('name')
            ->paginate(20);
        
        // 카테고리 목록
        $categories = DB::table('auth_email_templates')
            ->select('category')
            ->distinct()
            ->whereNotNull('category')
            ->pluck('category');
        
        // 통계
        $stats = [
            'total' => DB::table('auth_email_templates')->count(),
            'active' => DB::table('auth_email_templates')->where('is_active', true)->count(),
            'total_usage' => DB::table('auth_email_templates')->sum('usage_count'),
        ];
        
        return view('jiny-auth::admin.email-templates.index', compact('templates', 'categories', 'stats'));
    }
    
    /**
     * 템플릿 생성 폼
     * GET /admin/auth/emails/templates/create
     */
    public function create(Request $request)
    {
        $categories = DB::table('auth_email_templates')
            ->select('category')
            ->distinct()
            ->whereNotNull('category')
            ->pluck('category');
        
        return view('jiny-auth::admin.email-templates.create', compact('categories'));
    }
    
    /**
     * 템플릿 생성
     * POST /admin/auth/emails/templates
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:auth_email_templates,name',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'category' => 'nullable|string|max:50',
            'locale' => 'required|string|max:10',
            'is_active' => 'boolean',
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        // 변수 추출 ({{ variable }} 형식)
        $variables = [];
        if (preg_match_all('/\{\{\s*(\w+)\s*\}\}/', $request->body . ' ' . $request->subject, $matches)) {
            $variables = array_unique($matches[1]);
        }
        
        DB::table('auth_email_templates')->insert([
            'name' => $request->name,
            'subject' => $request->subject,
            'body' => $request->body,
            'category' => $request->category,
            'variables' => json_encode($variables),
            'locale' => $request->locale,
            'is_active' => $request->get('is_active', true),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        return redirect()->route('admin.auth.emails.templates')
            ->with('success', '이메일 템플릿이 생성되었습니다.');
    }
    
    /**
     * 템플릿 수정 폼
     * GET /admin/auth/emails/templates/{id}/edit
     */
    public function edit(Request $request, $id)
    {
        $template = DB::table('auth_email_templates')->where('id', $id)->first();
        
        if (!$template) {
            return redirect()->route('admin.auth.emails.templates')
                ->with('error', '템플릿을 찾을 수 없습니다.');
        }
        
        $template->variables = json_decode($template->variables, true) ?? [];
        
        $categories = DB::table('auth_email_templates')
            ->select('category')
            ->distinct()
            ->whereNotNull('category')
            ->pluck('category');
        
        return view('jiny-auth::admin.email-templates.edit', compact('template', 'categories'));
    }
    
    /**
     * 템플릿 수정
     * PUT /admin/auth/emails/templates/{id}
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:auth_email_templates,name,' . $id,
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'category' => 'nullable|string|max:50',
            'locale' => 'required|string|max:10',
            'is_active' => 'boolean',
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        $template = DB::table('auth_email_templates')->where('id', $id)->first();
        
        if (!$template) {
            return redirect()->route('admin.auth.emails.templates')
                ->with('error', '템플릿을 찾을 수 없습니다.');
        }
        
        // 변수 추출
        $variables = [];
        if (preg_match_all('/\{\{\s*(\w+)\s*\}\}/', $request->body . ' ' . $request->subject, $matches)) {
            $variables = array_unique($matches[1]);
        }
        
        DB::table('auth_email_templates')->where('id', $id)->update([
            'name' => $request->name,
            'subject' => $request->subject,
            'body' => $request->body,
            'category' => $request->category,
            'variables' => json_encode($variables),
            'locale' => $request->locale,
            'is_active' => $request->get('is_active', true),
            'updated_at' => now(),
        ]);
        
        return redirect()->route('admin.auth.emails.templates')
            ->with('success', '이메일 템플릿이 수정되었습니다.');
    }
    
    /**
     * 템플릿 삭제
     * DELETE /admin/auth/emails/templates/{id}
     */
    public function destroy(Request $request, $id)
    {
        $template = DB::table('auth_email_templates')->where('id', $id)->first();
        
        if (!$template) {
            return response()->json([
                'success' => false,
                'message' => '템플릿을 찾을 수 없습니다.'
            ], 404);
        }
        
        // 사용 중인지 확인
        if ($template->usage_count > 0) {
            return response()->json([
                'success' => false,
                'message' => "이 템플릿은 {$template->usage_count}번 사용되었습니다. 삭제하시겠습니까?",
                'confirm' => true
            ], 200);
        }
        
        DB::table('auth_email_templates')->where('id', $id)->delete();
        
        return response()->json([
            'success' => true,
            'message' => '템플릿이 삭제되었습니다.'
        ]);
    }
    
    /**
     * 템플릿 미리보기
     * GET /admin/auth/emails/templates/{id}/preview
     */
    public function preview(Request $request, $id)
    {
        $template = DB::table('auth_email_templates')->where('id', $id)->first();
        
        if (!$template) {
            return response()->json([
                'success' => false,
                'message' => '템플릿을 찾을 수 없습니다.'
            ], 404);
        }
        
        $variables = json_decode($template->variables, true) ?? [];
        
        // 샘플 데이터로 변수 치환
        $sampleData = [
            'user_name' => '홍길동',
            'site_name' => 'JinyAuth',
            'reset_link' => 'https://example.com/reset-password/token',
            'verify_link' => 'https://example.com/verify-email/token',
            'code' => '123456',
            'expire_minutes' => '10',
            'ip_address' => '192.168.1.1',
            'browser' => 'Chrome 120.0',
            'login_time' => now()->format('Y-m-d H:i:s'),
        ];
        
        $subject = $template->subject;
        $body = $template->body;
        
        foreach ($sampleData as $key => $value) {
            $subject = str_replace('{{ ' . $key . ' }}', $value, $subject);
            $body = str_replace('{{ ' . $key . ' }}', $value, $body);
        }
        
        return response()->json([
            'success' => true,
            'subject' => $subject,
            'body' => $body,
            'variables' => $variables
        ]);
    }
    
    /**
     * 템플릿 복제
     * POST /admin/auth/emails/templates/{id}/duplicate
     */
    public function duplicate(Request $request, $id)
    {
        $template = DB::table('auth_email_templates')->where('id', $id)->first();
        
        if (!$template) {
            return redirect()->route('admin.auth.emails.templates')
                ->with('error', '템플릿을 찾을 수 없습니다.');
        }
        
        // 새 이름 생성
        $newName = $template->name . '_copy';
        $counter = 1;
        while (DB::table('auth_email_templates')->where('name', $newName)->exists()) {
            $newName = $template->name . '_copy_' . $counter++;
        }
        
        DB::table('auth_email_templates')->insert([
            'name' => $newName,
            'subject' => $template->subject,
            'body' => $template->body,
            'category' => $template->category,
            'variables' => $template->variables,
            'locale' => $template->locale,
            'is_active' => false, // 복사본은 비활성화
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        return redirect()->route('admin.auth.emails.templates')
            ->with('success', '템플릿이 복제되었습니다.');
    }
}