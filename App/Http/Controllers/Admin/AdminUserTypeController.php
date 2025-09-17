<?php

namespace Jiny\Auth\App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class AdminUserTypeController extends Controller
{
    /**
     * 회원 유형 목록
     * GET /admin/auth/user-types
     */
    public function index(Request $request)
    {
        $types = DB::table('auth_user_types')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
        
        // 각 유형별 회원 수 계산
        foreach ($types as $type) {
            $type->user_count = User::where('user_type_id', $type->id)->count();
            $type->required_fields = json_decode($type->required_fields, true) ?? [];
            $type->optional_fields = json_decode($type->optional_fields, true) ?? [];
            $type->permissions = json_decode($type->permissions, true) ?? [];
            $type->restrictions = json_decode($type->restrictions, true) ?? [];
        }
        
        // 통계
        $stats = [
            'total_types' => count($types),
            'active_types' => $types->where('is_active', true)->count(),
            'approval_required' => $types->where('requires_approval', true)->count(),
            'verification_required' => $types->where('requires_verification', true)->count(),
        ];
        
        return view('jiny-auth::admin.user-types.index', compact('types', 'stats'));
    }
    
    /**
     * 유형 생성 폼
     * GET /admin/auth/user-types/create
     */
    public function create(Request $request)
    {
        $availableFields = [
            'name' => '이름',
            'email' => '이메일',
            'phone' => '전화번호',
            'company_name' => '회사명',
            'business_number' => '사업자번호',
            'contact_name' => '담당자명',
            'contact_email' => '담당자 이메일',
            'contact_phone' => '담당자 전화번호',
            'address' => '주소',
            'school' => '학교명',
            'student_id' => '학번',
            'region' => '지역',
            'department' => '부서',
            'position' => '직급',
        ];
        
        return view('jiny-auth::admin.user-types.create', compact('availableFields'));
    }
    
    /**
     * 유형 생성
     * POST /admin/auth/user-types
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:auth_user_types,name',
            'code' => 'required|string|max:20|unique:auth_user_types,code',
            'description' => 'nullable|string',
            'required_fields' => 'nullable|array',
            'optional_fields' => 'nullable|array',
            'permissions' => 'nullable|array',
            'restrictions' => 'nullable|array',
            'requires_approval' => 'boolean',
            'requires_verification' => 'boolean',
            'verification_type' => 'nullable|in:email,phone,document',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'icon' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:7',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        // 기본 유형 설정 시 기존 기본 유형 해제
        if ($request->get('is_default')) {
            DB::table('auth_user_types')->update(['is_default' => false]);
        }
        
        DB::table('auth_user_types')->insert([
            'name' => $request->name,
            'code' => $request->code,
            'description' => $request->description,
            'required_fields' => json_encode($request->get('required_fields', [])),
            'optional_fields' => json_encode($request->get('optional_fields', [])),
            'permissions' => json_encode($request->get('permissions', [])),
            'restrictions' => json_encode($request->get('restrictions', [])),
            'requires_approval' => $request->get('requires_approval', false),
            'requires_verification' => $request->get('requires_verification', false),
            'verification_type' => $request->verification_type,
            'commission_rate' => $request->commission_rate,
            'icon' => $request->icon,
            'color' => $request->color,
            'is_default' => $request->get('is_default', false),
            'is_active' => $request->get('is_active', true),
            'sort_order' => $request->get('sort_order', 0),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        return redirect()->route('admin.auth.user-types')
            ->with('success', '회원 유형이 생성되었습니다.');
    }
    
    /**
     * 유형 수정 폼
     * GET /admin/auth/user-types/{id}/edit
     */
    public function edit(Request $request, $id)
    {
        $type = DB::table('auth_user_types')->where('id', $id)->first();
        
        if (!$type) {
            return redirect()->route('admin.auth.user-types')
                ->with('error', '유형을 찾을 수 없습니다.');
        }
        
        $type->required_fields = json_decode($type->required_fields, true) ?? [];
        $type->optional_fields = json_decode($type->optional_fields, true) ?? [];
        $type->permissions = json_decode($type->permissions, true) ?? [];
        $type->restrictions = json_decode($type->restrictions, true) ?? [];
        
        $availableFields = [
            'name' => '이름',
            'email' => '이메일',
            'phone' => '전화번호',
            'company_name' => '회사명',
            'business_number' => '사업자번호',
            'contact_name' => '담당자명',
            'contact_email' => '담당자 이메일',
            'contact_phone' => '담당자 전화번호',
            'address' => '주소',
            'school' => '학교명',
            'student_id' => '학번',
            'region' => '지역',
            'department' => '부서',
            'position' => '직급',
        ];
        
        return view('jiny-auth::admin.user-types.edit', compact('type', 'availableFields'));
    }
    
    /**
     * 유형 수정
     * PUT /admin/auth/user-types/{id}
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:auth_user_types,name,' . $id,
            'code' => 'required|string|max:20|unique:auth_user_types,code,' . $id,
            'description' => 'nullable|string',
            'required_fields' => 'nullable|array',
            'optional_fields' => 'nullable|array',
            'permissions' => 'nullable|array',
            'restrictions' => 'nullable|array',
            'requires_approval' => 'boolean',
            'requires_verification' => 'boolean',
            'verification_type' => 'nullable|in:email,phone,document',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'icon' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:7',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        // 기본 유형 설정 시 기존 기본 유형 해제
        if ($request->get('is_default')) {
            DB::table('auth_user_types')->where('id', '!=', $id)->update(['is_default' => false]);
        }
        
        DB::table('auth_user_types')->where('id', $id)->update([
            'name' => $request->name,
            'code' => $request->code,
            'description' => $request->description,
            'required_fields' => json_encode($request->get('required_fields', [])),
            'optional_fields' => json_encode($request->get('optional_fields', [])),
            'permissions' => json_encode($request->get('permissions', [])),
            'restrictions' => json_encode($request->get('restrictions', [])),
            'requires_approval' => $request->get('requires_approval', false),
            'requires_verification' => $request->get('requires_verification', false),
            'verification_type' => $request->verification_type,
            'commission_rate' => $request->commission_rate,
            'icon' => $request->icon,
            'color' => $request->color,
            'is_default' => $request->get('is_default', false),
            'is_active' => $request->get('is_active', true),
            'sort_order' => $request->get('sort_order', 0),
            'updated_at' => now(),
        ]);
        
        return redirect()->route('admin.auth.user-types')
            ->with('success', '회원 유형이 수정되었습니다.');
    }
    
    /**
     * 유형 삭제
     * DELETE /admin/auth/user-types/{id}
     */
    public function destroy(Request $request, $id)
    {
        $type = DB::table('auth_user_types')->where('id', $id)->first();
        
        if (!$type) {
            return response()->json([
                'success' => false,
                'message' => '유형을 찾을 수 없습니다.'
            ], 404);
        }
        
        // 기본 유형은 삭제 불가
        if ($type->is_default) {
            return response()->json([
                'success' => false,
                'message' => '기본 유형은 삭제할 수 없습니다.'
            ], 400);
        }
        
        // 해당 유형을 사용 중인 회원 확인
        $userCount = User::where('user_type_id', $id)->count();
        if ($userCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "이 유형을 사용 중인 회원이 {$userCount}명 있습니다.",
                'confirm' => true
            ], 200);
        }
        
        DB::table('auth_user_types')->where('id', $id)->delete();
        
        return response()->json([
            'success' => true,
            'message' => '유형이 삭제되었습니다.'
        ]);
    }
    
    /**
     * 사용자 유형 변경
     * POST /admin/auth/users/{id}/type
     */
    public function changeUserType(Request $request, $userId)
    {
        $validator = Validator::make($request->all(), [
            'user_type_id' => 'required|exists:auth_user_types,id',
            'reason' => 'nullable|string|max:255',
            'verification_data' => 'nullable|array',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        $user = User::find($userId);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => '사용자를 찾을 수 없습니다.'
            ], 404);
        }
        
        $oldTypeId = $user->user_type_id;
        
        // 유형 변경
        $user->user_type_id = $request->user_type_id;
        $user->save();
        
        // 변경 로그 기록
        DB::table('auth_user_type_logs')->insert([
            'user_id' => $userId,
            'from_type_id' => $oldTypeId,
            'to_type_id' => $request->user_type_id,
            'reason' => $request->reason,
            'changed_by' => 'manual',
            'admin_id' => auth()->id(),
            'verification_data' => json_encode($request->get('verification_data', [])),
            'changed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        return response()->json([
            'success' => true,
            'message' => '회원 유형이 변경되었습니다.'
        ]);
    }
    
    /**
     * 유형별 통계
     * GET /admin/auth/user-types/statistics
     */
    public function statistics(Request $request)
    {
        // 유형별 회원 분포
        $distribution = DB::table('auth_user_types')
            ->leftJoin('users', 'auth_user_types.id', '=', 'users.user_type_id')
            ->select(
                'auth_user_types.id',
                'auth_user_types.name',
                'auth_user_types.code',
                'auth_user_types.color',
                'auth_user_types.commission_rate',
                DB::raw('COUNT(users.id) as user_count')
            )
            ->groupBy('auth_user_types.id', 'auth_user_types.name', 'auth_user_types.code', 'auth_user_types.color', 'auth_user_types.commission_rate')
            ->orderBy('auth_user_types.sort_order')
            ->get();
        
        // 승인/인증 대기 현황
        $pendingApproval = User::whereHas('userType', function ($query) {
            $query->where('requires_approval', true);
        })->where('is_approved', false)->count();
        
        $pendingVerification = User::whereHas('userType', function ($query) {
            $query->where('requires_verification', true);
        })->whereNull('email_verified_at')->count();
        
        // 최근 유형 변경 로그
        $recentChanges = DB::table('auth_user_type_logs')
            ->join('users', 'auth_user_type_logs.user_id', '=', 'users.id')
            ->leftJoin('auth_user_types as from_type', 'auth_user_type_logs.from_type_id', '=', 'from_type.id')
            ->leftJoin('auth_user_types as to_type', 'auth_user_type_logs.to_type_id', '=', 'to_type.id')
            ->select(
                'auth_user_type_logs.*',
                'users.name as user_name',
                'users.email as user_email',
                'from_type.name as from_type_name',
                'to_type.name as to_type_name'
            )
            ->orderBy('auth_user_type_logs.changed_at', 'desc')
            ->limit(50)
            ->get();
        
        return view('jiny-auth::admin.user-types.statistics', compact('distribution', 'pendingApproval', 'pendingVerification', 'recentChanges'));
    }
}