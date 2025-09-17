<?php

namespace Jiny\Auth\App\Http\Controllers\Admin\AuthCountries;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Auth\App\Models\Country;

class AuthCountriesShow extends Controller
{
    protected $viewPath = 'jiny-auth::admin.auth_countries';
    protected $jsonData;

    public function __construct()
    {
        $this->jsonData = $this->loadJsonData();
    }

    private function loadJsonData()
    {
        $jsonPath = __DIR__ . '/AuthCountries.json';
        if (file_exists($jsonPath)) {
            $jsonContent = file_get_contents($jsonPath);
            return json_decode($jsonContent, true);
        }
        
        return [];
    }

    public function index($id)
    {
        $this->jsonData['controllerClass'] = self::class;
        
        $country = Country::findOrFail($id);
        
        // Process data for display
        $country = $this->hookShowed(null, $country);
        
        return view('jiny-admin::crud.show', [
            'jsonData' => $this->jsonData,
            'viewPath' => $this->viewPath,
            'data' => $country,
            'id' => $id,
            'relatedData' => $this->hookRelatedData(null, $country),
        ]);
    }

    /**
     * Hook called before loading the record
     */
    public function hookShowing($wire, $id)
    {
        // Could add access control or logging here
        activity()
            ->withProperties(['country_id' => $id])
            ->log("국가 상세 정보 조회: ID {$id}");
    }

    /**
     * Hook called after loading the record
     */
    public function hookShowed($wire, $country)
    {
        // Add computed fields for display
        
        // Format languages
        if (is_array($country->languages)) {
            $country->languages_display = implode(', ', $country->languages);
        } else {
            $country->languages_display = $country->languages;
        }
        
        // Format timezones
        if (is_array($country->timezones)) {
            $country->timezones_display = implode(', ', $country->timezones);
        } else {
            $country->timezones_display = $country->timezones ?: $country->timezone;
        }
        
        // Format phone code
        if ($country->phone_code) {
            $country->phone_code_display = '+' . $country->phone_code;
        }
        
        // Format currency
        if ($country->currency_code && $country->currency_name) {
            $country->currency_display = "{$country->currency_name} ({$country->currency_code})";
            if ($country->currency_symbol) {
                $country->currency_display .= " {$country->currency_symbol}";
            }
        }
        
        // Get user statistics
        $country->statistics = [
            'total_users' => DB::table('accounts')
                ->where('country_id', $country->id)
                ->count(),
            'active_users' => DB::table('accounts')
                ->where('country_id', $country->id)
                ->where('is_active', true)
                ->count(),
            'new_users_this_month' => DB::table('accounts')
                ->where('country_id', $country->id)
                ->where('created_at', '>=', now()->startOfMonth())
                ->count(),
            'last_registration' => DB::table('accounts')
                ->where('country_id', $country->id)
                ->orderBy('created_at', 'desc')
                ->value('created_at'),
        ];
        
        // Get neighboring countries
        if ($country->region) {
            $country->neighbors = Country::where('region', $country->region)
                ->where('id', '!=', $country->id)
                ->where('is_active', true)
                ->orderBy('name')
                ->limit(10)
                ->get(['id', 'code', 'name', 'flag_emoji']);
        }
        
        return $country;
    }

    /**
     * Hook for loading related data
     */
    public function hookRelatedData($wire, $country)
    {
        $relatedData = [];
        
        // Recent users from this country
        $relatedData['recent_users'] = DB::table('accounts')
            ->where('country_id', $country->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get(['id', 'name', 'email', 'created_at', 'is_active']);
        
        // Countries with same currency
        if ($country->currency_code) {
            $relatedData['same_currency'] = Country::where('currency_code', $country->currency_code)
                ->where('id', '!=', $country->id)
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'code', 'name', 'flag_emoji']);
        }
        
        // Countries in same subregion
        if ($country->subregion) {
            $relatedData['same_subregion'] = Country::where('subregion', $country->subregion)
                ->where('id', '!=', $country->id)
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'code', 'name', 'flag_emoji']);
        }
        
        // Activity logs for this country
        $relatedData['activity_logs'] = DB::table('activity_log')
            ->where('subject_type', Country::class)
            ->where('subject_id', $country->id)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get(['description', 'created_at', 'causer_id']);
        
        return $relatedData;
    }

    /**
     * Hook for customizing detail fields display
     */
    public function hookDetailFields($wire)
    {
        return [
            'basic_info' => [
                'title' => '기본 정보',
                'fields' => [
                    'code' => ['label' => 'ISO2 코드', 'badge' => true],
                    'code3' => ['label' => 'ISO3 코드', 'badge' => true],
                    'numeric_code' => ['label' => '숫자 코드'],
                    'name' => ['label' => '국가명', 'bold' => true],
                    'native_name' => ['label' => '현지 국가명'],
                    'flag_emoji' => ['label' => '국기', 'large' => true],
                ],
            ],
            'geographic_info' => [
                'title' => '지리 정보',
                'fields' => [
                    'capital' => ['label' => '수도'],
                    'region' => ['label' => '대륙', 'badge' => true],
                    'subregion' => ['label' => '하위 지역'],
                    'latitude' => ['label' => '위도'],
                    'longitude' => ['label' => '경도'],
                ],
            ],
            'economic_info' => [
                'title' => '경제 정보',
                'fields' => [
                    'currency_display' => ['label' => '통화'],
                    'phone_code_display' => ['label' => '국가번호', 'badge' => true],
                ],
            ],
            'cultural_info' => [
                'title' => '문화 정보',
                'fields' => [
                    'languages_display' => ['label' => '언어'],
                    'timezone' => ['label' => '시간대'],
                    'timezones_display' => ['label' => '모든 시간대'],
                ],
            ],
            'system_info' => [
                'title' => '시스템 정보',
                'fields' => [
                    'display_order' => ['label' => '표시 순서', 'badge' => true],
                    'is_active' => ['label' => '활성화 상태', 'boolean' => true],
                    'created_at' => ['label' => '생성일', 'datetime' => true],
                    'updated_at' => ['label' => '수정일', 'datetime' => true],
                ],
            ],
        ];
    }

    /**
     * Custom action to toggle country status
     */
    public function toggleStatus($id)
    {
        $country = Country::findOrFail($id);
        
        // Check if can deactivate
        if ($country->is_active) {
            $activeUsers = DB::table('accounts')
                ->where('country_id', $country->id)
                ->where('is_active', true)
                ->count();
            
            if ($activeUsers > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "활성 사용자가 {$activeUsers}명 있어 비활성화할 수 없습니다.",
                ]);
            }
        }
        
        $country->is_active = !$country->is_active;
        $country->save();
        
        $status = $country->is_active ? '활성화' : '비활성화';
        
        activity()
            ->performedOn($country)
            ->log("국가 '{$country->name}'가 {$status}되었습니다");
        
        return response()->json([
            'success' => true,
            'message' => "국가가 {$status}되었습니다.",
            'is_active' => $country->is_active,
        ]);
    }
}