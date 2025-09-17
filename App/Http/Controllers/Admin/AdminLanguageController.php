<?php

namespace Jiny\Auth\App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AdminLanguageController extends Controller
{
    /**
     * 언어 목록
     * GET /admin/auth/languages
     */
    public function index(Request $request)
    {
        $query = DB::table('languages');
        
        // 검색
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('native_name', 'like', "%{$search}%");
            });
        }
        
        // 필터
        if ($request->has('is_active')) {
            $query->where('is_active', $request->get('is_active') === 'true');
        }
        
        if ($request->has('direction')) {
            $query->where('direction', $request->get('direction'));
        }
        
        $languages = $query->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20);
        
        // 통계
        $stats = [
            'total' => DB::table('languages')->count(),
            'active' => DB::table('languages')->where('is_active', true)->count(),
            'inactive' => DB::table('languages')->where('is_active', false)->count(),
            'users_with_settings' => DB::table('user_language_settings')->distinct('user_id')->count('user_id'),
        ];
        
        return view('jiny-auth::admin.languages.index', compact('languages', 'stats'));
    }
    
    /**
     * 언어 추가 폼
     * GET /admin/auth/languages/create
     */
    public function create(Request $request)
    {
        return view('jiny-auth::admin.languages.create');
    }
    
    /**
     * 언어 추가
     * POST /admin/auth/languages
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:10|unique:languages,code',
            'name' => 'required|string|max:255',
            'native_name' => 'required|string|max:255',
            'direction' => 'required|in:ltr,rtl',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'sort_order' => 'integer',
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        // 기본 언어 설정 시 기존 기본 언어 해제
        if ($request->get('is_default')) {
            DB::table('languages')->where('is_default', true)
                ->update(['is_default' => false]);
        }
        
        $localeSettings = [
            'date_format' => $request->get('date_format', 'Y-m-d'),
            'time_format' => $request->get('time_format', 'H:i:s'),
            'datetime_format' => $request->get('datetime_format', 'Y-m-d H:i:s'),
            'number_decimal' => $request->get('number_decimal', '.'),
            'number_thousands' => $request->get('number_thousands', ','),
        ];
        
        DB::table('languages')->insert([
            'code' => $request->code,
            'name' => $request->name,
            'native_name' => $request->native_name,
            'direction' => $request->direction,
            'is_active' => $request->get('is_active', true),
            'is_default' => $request->get('is_default', false),
            'sort_order' => $request->get('sort_order', 0),
            'locale_settings' => json_encode($localeSettings),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        return redirect()->route('admin.auth.languages')
            ->with('success', '언어가 추가되었습니다.');
    }
    
    /**
     * 언어 수정 폼
     * GET /admin/auth/languages/{id}/edit
     */
    public function edit(Request $request, $id)
    {
        $language = DB::table('languages')->where('id', $id)->first();
        
        if (!$language) {
            return redirect()->route('admin.auth.languages')
                ->with('error', '언어를 찾을 수 없습니다.');
        }
        
        $language->locale_settings = json_decode($language->locale_settings, true) ?? [];
        
        return view('jiny-auth::admin.languages.edit', compact('language'));
    }
    
    /**
     * 언어 수정
     * PUT /admin/auth/languages/{id}
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:10|unique:languages,code,' . $id,
            'name' => 'required|string|max:255',
            'native_name' => 'required|string|max:255',
            'direction' => 'required|in:ltr,rtl',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'sort_order' => 'integer',
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        $language = DB::table('languages')->where('id', $id)->first();
        
        if (!$language) {
            return redirect()->route('admin.auth.languages')
                ->with('error', '언어를 찾을 수 없습니다.');
        }
        
        // 기본 언어 설정 시 기존 기본 언어 해제
        if ($request->get('is_default') && !$language->is_default) {
            DB::table('languages')->where('is_default', true)
                ->update(['is_default' => false]);
        }
        
        $localeSettings = [
            'date_format' => $request->get('date_format', 'Y-m-d'),
            'time_format' => $request->get('time_format', 'H:i:s'),
            'datetime_format' => $request->get('datetime_format', 'Y-m-d H:i:s'),
            'number_decimal' => $request->get('number_decimal', '.'),
            'number_thousands' => $request->get('number_thousands', ','),
        ];
        
        DB::table('languages')->where('id', $id)->update([
            'code' => $request->code,
            'name' => $request->name,
            'native_name' => $request->native_name,
            'direction' => $request->direction,
            'is_active' => $request->get('is_active', true),
            'is_default' => $request->get('is_default', false),
            'sort_order' => $request->get('sort_order', 0),
            'locale_settings' => json_encode($localeSettings),
            'updated_at' => now(),
        ]);
        
        return redirect()->route('admin.auth.languages')
            ->with('success', '언어가 수정되었습니다.');
    }
    
    /**
     * 언어 삭제
     * DELETE /admin/auth/languages/{id}
     */
    public function destroy(Request $request, $id)
    {
        $language = DB::table('languages')->where('id', $id)->first();
        
        if (!$language) {
            return response()->json([
                'success' => false,
                'message' => '언어를 찾을 수 없습니다.'
            ], 404);
        }
        
        // 기본 언어는 삭제 불가
        if ($language->is_default) {
            return response()->json([
                'success' => false,
                'message' => '기본 언어는 삭제할 수 없습니다.'
            ], 400);
        }
        
        // 사용 중인 언어 확인
        $usersCount = DB::table('user_language_settings')
            ->where('language_id', $id)
            ->count();
        
        if ($usersCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "이 언어를 사용 중인 사용자가 {$usersCount}명 있습니다."
            ], 400);
        }
        
        DB::table('languages')->where('id', $id)->delete();
        
        return response()->json([
            'success' => true,
            'message' => '언어가 삭제되었습니다.'
        ]);
    }
    
    /**
     * 언어 순서 변경
     * POST /admin/auth/languages/reorder
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'languages' => 'required|array',
            'languages.*.id' => 'required|exists:languages,id',
            'languages.*.sort_order' => 'required|integer',
        ]);
        
        foreach ($request->languages as $item) {
            DB::table('languages')
                ->where('id', $item['id'])
                ->update(['sort_order' => $item['sort_order']]);
        }
        
        return response()->json([
            'success' => true,
            'message' => '언어 순서가 변경되었습니다.'
        ]);
    }
    
    /**
     * 언어별 사용자 통계
     * GET /admin/auth/languages/{id}/users
     */
    public function users(Request $request, $id)
    {
        $language = DB::table('languages')->where('id', $id)->first();
        
        if (!$language) {
            return redirect()->route('admin.auth.languages')
                ->with('error', '언어를 찾을 수 없습니다.');
        }
        
        $users = DB::table('user_language_settings')
            ->join('users', 'user_language_settings.user_id', '=', 'users.id')
            ->leftJoin('countries', 'user_language_settings.country_id', '=', 'countries.id')
            ->where('user_language_settings.language_id', $id)
            ->select(
                'users.id',
                'users.name',
                'users.email',
                'countries.name as country_name',
                'user_language_settings.timezone',
                'user_language_settings.created_at'
            )
            ->paginate(20);
        
        return view('jiny-auth::admin.languages.users', compact('language', 'users'));
    }
}