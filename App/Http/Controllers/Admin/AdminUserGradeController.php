<?php

namespace Jiny\Auth\App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class AdminUserGradeController extends Controller
{
    /**
     * 회원 등급 목록
     * GET /admin/auth/grades
     */
    public function index(Request $request)
    {
        $grades = DB::table('auth_user_grades')
            ->orderBy('sort_order')
            ->orderBy('level')
            ->get();
        
        // 각 등급별 회원 수 계산
        foreach ($grades as $grade) {
            $grade->user_count = User::where('grade_id', $grade->id)->count();
            $grade->benefits = json_decode($grade->benefits, true) ?? [];
            $grade->permissions = json_decode($grade->permissions, true) ?? [];
        }
        
        // 통계
        $stats = [
            'total_grades' => count($grades),
            'active_grades' => $grades->where('is_active', true)->count(),
            'total_users_with_grade' => User::whereNotNull('grade_id')->count(),
        ];
        
        return view('jiny-auth::admin.grades.index', compact('grades', 'stats'));
    }
    
    /**
     * 등급 생성 폼
     * GET /admin/auth/grades/create
     */
    public function create(Request $request)
    {
        return view('jiny-auth::admin.grades.create');
    }
    
    /**
     * 등급 생성
     * POST /admin/auth/grades
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:auth_user_grades,name',
            'code' => 'required|string|max:20|unique:auth_user_grades,code',
            'description' => 'nullable|string',
            'level' => 'required|integer|min:1|max:100',
            'discount_rate' => 'nullable|numeric|min:0|max:100',
            'point_rate' => 'nullable|numeric|min:0|max:10',
            'upgrade_amount' => 'nullable|numeric|min:0',
            'upgrade_count' => 'nullable|integer|min:0',
            'benefits' => 'nullable|array',
            'permissions' => 'nullable|array',
            'badge_color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:255',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        // 기본 등급 설정 시 기존 기본 등급 해제
        if ($request->get('is_default')) {
            DB::table('auth_user_grades')->update(['is_default' => false]);
        }
        
        DB::table('auth_user_grades')->insert([
            'name' => $request->name,
            'code' => $request->code,
            'description' => $request->description,
            'level' => $request->level,
            'discount_rate' => $request->get('discount_rate', 0),
            'point_rate' => $request->get('point_rate', 1),
            'upgrade_amount' => $request->upgrade_amount,
            'upgrade_count' => $request->upgrade_count,
            'benefits' => json_encode($request->get('benefits', [])),
            'permissions' => json_encode($request->get('permissions', [])),
            'badge_color' => $request->badge_color,
            'icon' => $request->icon,
            'is_default' => $request->get('is_default', false),
            'is_active' => $request->get('is_active', true),
            'sort_order' => $request->get('sort_order', 0),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        return redirect()->route('admin.auth.grades')
            ->with('success', '회원 등급이 생성되었습니다.');
    }
    
    /**
     * 등급 수정 폼
     * GET /admin/auth/grades/{id}/edit
     */
    public function edit(Request $request, $id)
    {
        $grade = DB::table('auth_user_grades')->where('id', $id)->first();
        
        if (!$grade) {
            return redirect()->route('admin.auth.grades')
                ->with('error', '등급을 찾을 수 없습니다.');
        }
        
        $grade->benefits = json_decode($grade->benefits, true) ?? [];
        $grade->permissions = json_decode($grade->permissions, true) ?? [];
        
        return view('jiny-auth::admin.grades.edit', compact('grade'));
    }
    
    /**
     * 등급 수정
     * PUT /admin/auth/grades/{id}
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:auth_user_grades,name,' . $id,
            'code' => 'required|string|max:20|unique:auth_user_grades,code,' . $id,
            'description' => 'nullable|string',
            'level' => 'required|integer|min:1|max:100',
            'discount_rate' => 'nullable|numeric|min:0|max:100',
            'point_rate' => 'nullable|numeric|min:0|max:10',
            'upgrade_amount' => 'nullable|numeric|min:0',
            'upgrade_count' => 'nullable|integer|min:0',
            'benefits' => 'nullable|array',
            'permissions' => 'nullable|array',
            'badge_color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:255',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        // 기본 등급 설정 시 기존 기본 등급 해제
        if ($request->get('is_default')) {
            DB::table('auth_user_grades')->where('id', '!=', $id)->update(['is_default' => false]);
        }
        
        DB::table('auth_user_grades')->where('id', $id)->update([
            'name' => $request->name,
            'code' => $request->code,
            'description' => $request->description,
            'level' => $request->level,
            'discount_rate' => $request->get('discount_rate', 0),
            'point_rate' => $request->get('point_rate', 1),
            'upgrade_amount' => $request->upgrade_amount,
            'upgrade_count' => $request->upgrade_count,
            'benefits' => json_encode($request->get('benefits', [])),
            'permissions' => json_encode($request->get('permissions', [])),
            'badge_color' => $request->badge_color,
            'icon' => $request->icon,
            'is_default' => $request->get('is_default', false),
            'is_active' => $request->get('is_active', true),
            'sort_order' => $request->get('sort_order', 0),
            'updated_at' => now(),
        ]);
        
        return redirect()->route('admin.auth.grades')
            ->with('success', '회원 등급이 수정되었습니다.');
    }
    
    /**
     * 등급 삭제
     * DELETE /admin/auth/grades/{id}
     */
    public function destroy(Request $request, $id)
    {
        $grade = DB::table('auth_user_grades')->where('id', $id)->first();
        
        if (!$grade) {
            return response()->json([
                'success' => false,
                'message' => '등급을 찾을 수 없습니다.'
            ], 404);
        }
        
        // 기본 등급은 삭제 불가
        if ($grade->is_default) {
            return response()->json([
                'success' => false,
                'message' => '기본 등급은 삭제할 수 없습니다.'
            ], 400);
        }
        
        // 해당 등급을 사용 중인 회원 확인
        $userCount = User::where('grade_id', $id)->count();
        if ($userCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "이 등급을 사용 중인 회원이 {$userCount}명 있습니다.",
                'confirm' => true
            ], 200);
        }
        
        DB::table('auth_user_grades')->where('id', $id)->delete();
        
        return response()->json([
            'success' => true,
            'message' => '등급이 삭제되었습니다.'
        ]);
    }
    
    /**
     * 사용자 등급 변경
     * POST /admin/auth/users/{id}/grade
     */
    public function changeUserGrade(Request $request, $userId)
    {
        $validator = Validator::make($request->all(), [
            'grade_id' => 'required|exists:auth_user_grades,id',
            'reason' => 'nullable|string|max:255',
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
        
        $oldGradeId = $user->grade_id;
        
        // 등급 변경
        $user->grade_id = $request->grade_id;
        $user->grade_updated_at = now();
        $user->save();
        
        // 변경 로그 기록
        DB::table('auth_user_grade_logs')->insert([
            'user_id' => $userId,
            'from_grade_id' => $oldGradeId,
            'to_grade_id' => $request->grade_id,
            'reason' => $request->reason,
            'changed_by' => 'manual',
            'admin_id' => auth()->id(),
            'changed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        return response()->json([
            'success' => true,
            'message' => '회원 등급이 변경되었습니다.'
        ]);
    }
    
    /**
     * 등급 통계
     * GET /admin/auth/grades/statistics
     */
    public function statistics(Request $request)
    {
        // 등급별 회원 분포
        $distribution = DB::table('auth_user_grades')
            ->leftJoin('users', 'auth_user_grades.id', '=', 'users.grade_id')
            ->select(
                'auth_user_grades.id',
                'auth_user_grades.name',
                'auth_user_grades.code',
                'auth_user_grades.level',
                'auth_user_grades.badge_color',
                DB::raw('COUNT(users.id) as user_count')
            )
            ->groupBy('auth_user_grades.id', 'auth_user_grades.name', 'auth_user_grades.code', 'auth_user_grades.level', 'auth_user_grades.badge_color')
            ->orderBy('auth_user_grades.level')
            ->get();
        
        // 최근 등급 변경 로그
        $recentChanges = DB::table('auth_user_grade_logs')
            ->join('users', 'auth_user_grade_logs.user_id', '=', 'users.id')
            ->leftJoin('auth_user_grades as from_grade', 'auth_user_grade_logs.from_grade_id', '=', 'from_grade.id')
            ->leftJoin('auth_user_grades as to_grade', 'auth_user_grade_logs.to_grade_id', '=', 'to_grade.id')
            ->select(
                'auth_user_grade_logs.*',
                'users.name as user_name',
                'users.email as user_email',
                'from_grade.name as from_grade_name',
                'to_grade.name as to_grade_name'
            )
            ->orderBy('auth_user_grade_logs.changed_at', 'desc')
            ->limit(50)
            ->get();
        
        // 월별 등급 변경 추이
        $monthlyChanges = DB::table('auth_user_grade_logs')
            ->select(
                DB::raw('DATE_FORMAT(changed_at, "%Y-%m") as month'),
                DB::raw('COUNT(*) as total_changes'),
                DB::raw('COUNT(DISTINCT user_id) as unique_users')
            )
            ->where('changed_at', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->get();
        
        return view('jiny-auth::admin.grades.statistics', compact('distribution', 'recentChanges', 'monthlyChanges'));
    }
    
    /**
     * 자동 등급 업그레이드 처리
     * 스케줄러에서 호출
     */
    public function processAutoUpgrade()
    {
        $grades = DB::table('auth_user_grades')
            ->where('is_active', true)
            ->whereNotNull('upgrade_amount')
            ->orderBy('level')
            ->get();
        
        foreach ($grades as $grade) {
            $nextGrade = DB::table('auth_user_grades')
                ->where('level', '>', $grade->level)
                ->where('is_active', true)
                ->orderBy('level')
                ->first();
            
            if (!$nextGrade) continue;
            
            // 조건을 만족하는 사용자 찾기
            $users = User::where('grade_id', $grade->id)
                ->where('total_purchase_amount', '>=', $nextGrade->upgrade_amount);
            
            if ($nextGrade->upgrade_count) {
                $users->where('total_purchase_count', '>=', $nextGrade->upgrade_count);
            }
            
            $users = $users->get();
            
            foreach ($users as $user) {
                // 등급 업그레이드
                $user->grade_id = $nextGrade->id;
                $user->grade_updated_at = now();
                $user->save();
                
                // 로그 기록
                DB::table('auth_user_grade_logs')->insert([
                    'user_id' => $user->id,
                    'from_grade_id' => $grade->id,
                    'to_grade_id' => $nextGrade->id,
                    'reason' => '자동 승급 (구매 금액: ' . number_format($user->total_purchase_amount) . '원)',
                    'changed_by' => 'system',
                    'changed_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        
        return [
            'success' => true,
            'message' => '자동 등급 업그레이드가 완료되었습니다.'
        ];
    }
}