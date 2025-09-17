<?php

namespace Jiny\Auth\App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AdminCountryController extends Controller
{
    /**
     * 국가 목록
     * GET /admin/auth/countries
     */
    public function index(Request $request)
    {
        $query = DB::table('countries');
        
        // 검색
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('code3', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('native_name', 'like', "%{$search}%")
                  ->orWhere('capital', 'like', "%{$search}%");
            });
        }
        
        // 필터
        if ($request->has('region')) {
            $query->where('region', $request->get('region'));
        }
        
        if ($request->has('is_active')) {
            $query->where('is_active', $request->get('is_active') === 'true');
        }
        
        if ($request->has('currency_code')) {
            $query->where('currency_code', $request->get('currency_code'));
        }
        
        $countries = $query->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20);
        
        // 통계
        $stats = [
            'total' => DB::table('countries')->count(),
            'active' => DB::table('countries')->where('is_active', true)->count(),
            'inactive' => DB::table('countries')->where('is_active', false)->count(),
            'regions' => DB::table('countries')
                ->select('region', DB::raw('COUNT(*) as count'))
                ->groupBy('region')
                ->get(),
        ];
        
        return view('jiny-auth::admin.countries.index', compact('countries', 'stats'));
    }
    
    /**
     * 국가 추가 폼
     * GET /admin/auth/countries/create
     */
    public function create(Request $request)
    {
        $languages = DB::table('languages')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        
        return view('jiny-auth::admin.countries.create', compact('languages'));
    }
    
    /**
     * 국가 추가
     * POST /admin/auth/countries
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|size:2|unique:countries,code',
            'code3' => 'required|string|size:3|unique:countries,code3',
            'numeric_code' => 'required|string|size:3',
            'name' => 'required|string|max:255',
            'native_name' => 'required|string|max:255',
            'capital' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:255',
            'subregion' => 'nullable|string|max:255',
            'currency_code' => 'nullable|string|size:3',
            'currency_name' => 'nullable|string|max:255',
            'currency_symbol' => 'nullable|string|max:10',
            'phone_code' => 'nullable|string|max:10',
            'timezone' => 'nullable|string|max:50',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'flag_emoji' => 'nullable|string|max:10',
            'flag_url' => 'nullable|url',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        // 시간대 및 언어 처리
        $timezones = $request->get('timezones', []);
        if (is_string($timezones)) {
            $timezones = array_map('trim', explode(',', $timezones));
        }
        
        $languages = $request->get('languages', []);
        if (is_string($languages)) {
            $languages = array_map('trim', explode(',', $languages));
        }
        
        DB::table('countries')->insert([
            'code' => strtoupper($request->code),
            'code3' => strtoupper($request->code3),
            'numeric_code' => $request->numeric_code,
            'name' => $request->name,
            'native_name' => $request->native_name,
            'capital' => $request->capital,
            'region' => $request->region,
            'subregion' => $request->subregion,
            'currency_code' => strtoupper($request->currency_code),
            'currency_name' => $request->currency_name,
            'currency_symbol' => $request->currency_symbol,
            'phone_code' => $request->phone_code,
            'timezone' => $request->timezone,
            'timezones' => json_encode($timezones),
            'languages' => json_encode($languages),
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'flag_emoji' => $request->flag_emoji,
            'flag_url' => $request->flag_url,
            'is_active' => $request->get('is_active', true),
            'sort_order' => $request->get('sort_order', 0),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        return redirect()->route('admin.auth.countries')
            ->with('success', '국가가 추가되었습니다.');
    }
    
    /**
     * 국가 수정 폼
     * GET /admin/auth/countries/{id}/edit
     */
    public function edit(Request $request, $id)
    {
        $country = DB::table('countries')->where('id', $id)->first();
        
        if (!$country) {
            return redirect()->route('admin.auth.countries')
                ->with('error', '국가를 찾을 수 없습니다.');
        }
        
        $country->timezones = json_decode($country->timezones, true) ?? [];
        $country->languages = json_decode($country->languages, true) ?? [];
        
        $languages = DB::table('languages')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        
        return view('jiny-auth::admin.countries.edit', compact('country', 'languages'));
    }
    
    /**
     * 국가 수정
     * PUT /admin/auth/countries/{id}
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|size:2|unique:countries,code,' . $id,
            'code3' => 'required|string|size:3|unique:countries,code3,' . $id,
            'numeric_code' => 'required|string|size:3',
            'name' => 'required|string|max:255',
            'native_name' => 'required|string|max:255',
            'capital' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:255',
            'subregion' => 'nullable|string|max:255',
            'currency_code' => 'nullable|string|size:3',
            'currency_name' => 'nullable|string|max:255',
            'currency_symbol' => 'nullable|string|max:10',
            'phone_code' => 'nullable|string|max:10',
            'timezone' => 'nullable|string|max:50',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'flag_emoji' => 'nullable|string|max:10',
            'flag_url' => 'nullable|url',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        $country = DB::table('countries')->where('id', $id)->first();
        
        if (!$country) {
            return redirect()->route('admin.auth.countries')
                ->with('error', '국가를 찾을 수 없습니다.');
        }
        
        // 시간대 및 언어 처리
        $timezones = $request->get('timezones', []);
        if (is_string($timezones)) {
            $timezones = array_map('trim', explode(',', $timezones));
        }
        
        $languages = $request->get('languages', []);
        if (is_string($languages)) {
            $languages = array_map('trim', explode(',', $languages));
        }
        
        DB::table('countries')->where('id', $id)->update([
            'code' => strtoupper($request->code),
            'code3' => strtoupper($request->code3),
            'numeric_code' => $request->numeric_code,
            'name' => $request->name,
            'native_name' => $request->native_name,
            'capital' => $request->capital,
            'region' => $request->region,
            'subregion' => $request->subregion,
            'currency_code' => strtoupper($request->currency_code),
            'currency_name' => $request->currency_name,
            'currency_symbol' => $request->currency_symbol,
            'phone_code' => $request->phone_code,
            'timezone' => $request->timezone,
            'timezones' => json_encode($timezones),
            'languages' => json_encode($languages),
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'flag_emoji' => $request->flag_emoji,
            'flag_url' => $request->flag_url,
            'is_active' => $request->get('is_active', true),
            'sort_order' => $request->get('sort_order', 0),
            'updated_at' => now(),
        ]);
        
        return redirect()->route('admin.auth.countries')
            ->with('success', '국가가 수정되었습니다.');
    }
    
    /**
     * 국가 삭제
     * DELETE /admin/auth/countries/{id}
     */
    public function destroy(Request $request, $id)
    {
        $country = DB::table('countries')->where('id', $id)->first();
        
        if (!$country) {
            return response()->json([
                'success' => false,
                'message' => '국가를 찾을 수 없습니다.'
            ], 404);
        }
        
        // 사용 중인 국가 확인
        $usersCount = DB::table('user_language_settings')
            ->where('country_id', $id)
            ->count();
        
        if ($usersCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "이 국가를 사용 중인 사용자가 {$usersCount}명 있습니다."
            ], 400);
        }
        
        DB::table('countries')->where('id', $id)->delete();
        
        return response()->json([
            'success' => true,
            'message' => '국가가 삭제되었습니다.'
        ]);
    }
    
    /**
     * 국가별 통계
     * GET /admin/auth/countries/statistics
     */
    public function statistics(Request $request)
    {
        // 지역별 통계
        $regionStats = DB::table('countries')
            ->select(
                'region',
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active')
            )
            ->groupBy('region')
            ->orderBy('total', 'desc')
            ->get();
        
        // 통화별 통계
        $currencyStats = DB::table('countries')
            ->select(
                'currency_code',
                'currency_name',
                DB::raw('COUNT(*) as country_count')
            )
            ->whereNotNull('currency_code')
            ->groupBy('currency_code', 'currency_name')
            ->orderBy('country_count', 'desc')
            ->limit(10)
            ->get();
        
        // 언어별 국가 수
        $languageStats = [];
        $countries = DB::table('countries')->get();
        foreach ($countries as $country) {
            $langs = json_decode($country->languages, true) ?? [];
            foreach ($langs as $lang) {
                if (!isset($languageStats[$lang])) {
                    $languageStats[$lang] = 0;
                }
                $languageStats[$lang]++;
            }
        }
        arsort($languageStats);
        
        // 사용자 분포
        $userDistribution = DB::table('user_language_settings')
            ->join('countries', 'user_language_settings.country_id', '=', 'countries.id')
            ->select(
                'countries.name',
                'countries.code',
                'countries.flag_emoji',
                DB::raw('COUNT(user_language_settings.user_id) as user_count')
            )
            ->groupBy('countries.id', 'countries.name', 'countries.code', 'countries.flag_emoji')
            ->orderBy('user_count', 'desc')
            ->limit(20)
            ->get();
        
        return view('jiny-auth::admin.countries.statistics', compact(
            'regionStats',
            'currencyStats',
            'languageStats',
            'userDistribution'
        ));
    }
    
    /**
     * 국가 가져오기 (외부 API)
     * POST /admin/auth/countries/import
     */
    public function import(Request $request)
    {
        // REST Countries API 또는 다른 소스에서 국가 정보 가져오기
        // 구현 예정
        
        return response()->json([
            'success' => false,
            'message' => '국가 가져오기 기능은 준비 중입니다.'
        ]);
    }
}