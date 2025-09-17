<?php

namespace Jiny\Auth\App\Http\Controllers\Admin\AuthCountries;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Auth\App\Models\Country;

class AuthCountries extends Controller
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
        
        return [
            'title' => '국가 관리',
            'description' => '지역 및 국가 코드 관리',
            'model' => 'Jiny\\Auth\\App\\Models\\Country',
            'table' => 'countries',
            'primaryKey' => 'id',
            'perPage' => 30,
            'searchable' => ['name', 'native_name', 'code', 'code3', 'phone_code'],
            'sortable' => ['name', 'code', 'code3', 'display_order', 'is_active'],
        ];
    }

    public function index(Request $request)
    {
        $this->jsonData['controllerClass'] = self::class;
        
        return view('jiny-admin::crud.index', [
            'jsonData' => $this->jsonData,
            'actions' => [
                'create' => route('admin.auth.countries.create'),
                'import' => route('admin.auth.countries.import'),
                'statistics' => route('admin.auth.countries.statistics'),
            ]
        ]);
    }

    /**
     * Hook for customizing the query before execution
     */
    public function hookIndexing($wire)
    {
        // Add default sorting by display_order and name
        if (!$wire->sortField) {
            $wire->sortField = 'display_order';
            $wire->sortDirection = 'asc';
        }
    }

    /**
     * Hook for processing data after retrieval
     */
    public function hookIndexed($wire, $rows)
    {
        // Add user count for each country
        foreach ($rows as $row) {
            $row->user_count = DB::table('accounts')
                ->where('country_id', $row->id)
                ->count();
            
            // Format display data
            if ($row->languages) {
                $row->languages_display = is_array($row->languages) 
                    ? implode(', ', $row->languages) 
                    : $row->languages;
            }
            
            if ($row->phone_code) {
                $row->phone_code_display = '+' . $row->phone_code;
            }
        }
        
        return $rows;
    }

    /**
     * Import countries from CSV
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('file');
        $data = array_map('str_getcsv', file($file->getRealPath()));
        $header = array_shift($data);

        $imported = 0;
        $failed = [];

        foreach ($data as $row) {
            try {
                $countryData = array_combine($header, $row);
                
                // Process the data
                $countryData = $this->hookImporting($countryData);
                
                Country::updateOrCreate(
                    ['code' => $countryData['code']],
                    $countryData
                );
                
                $imported++;
            } catch (\Exception $e) {
                $failed[] = [
                    'row' => $row,
                    'error' => $e->getMessage()
                ];
            }
        }

        return redirect()->route('admin.auth.countries')
            ->with('success', "{$imported}개 국가가 가져오기되었습니다.")
            ->with('failed', $failed);
    }

    /**
     * Hook for processing imported data
     */
    public function hookImporting($data)
    {
        // Ensure ISO codes are uppercase
        if (isset($data['code'])) {
            $data['code'] = strtoupper($data['code']);
        }
        if (isset($data['code3'])) {
            $data['code3'] = strtoupper($data['code3']);
        }
        if (isset($data['currency_code'])) {
            $data['currency_code'] = strtoupper($data['currency_code']);
        }
        
        // Parse languages if it's a string
        if (isset($data['languages']) && is_string($data['languages'])) {
            $data['languages'] = array_map('trim', explode(',', $data['languages']));
        }
        
        // Parse timezones if it's a string
        if (isset($data['timezones']) && is_string($data['timezones'])) {
            $data['timezones'] = array_map('trim', explode(',', $data['timezones']));
        }
        
        // Set default display order for popular countries
        $popularCountries = ['KR', 'US', 'JP', 'CN', 'GB', 'DE', 'FR', 'CA', 'AU'];
        if (isset($data['code']) && in_array($data['code'], $popularCountries)) {
            $data['display_order'] = array_search($data['code'], $popularCountries) + 1;
        } else {
            $data['display_order'] = $data['display_order'] ?? 999;
        }
        
        return $data;
    }

    /**
     * Bulk toggle active status
     */
    public function bulkToggle(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:countries,id',
            'is_active' => 'required|boolean',
        ]);

        Country::whereIn('id', $request->ids)
            ->update(['is_active' => $request->is_active]);

        return response()->json([
            'success' => true,
            'message' => count($request->ids) . '개 국가의 상태가 변경되었습니다.',
        ]);
    }

    /**
     * Display statistics page
     */
    public function statistics()
    {
        $stats = [
            'total_countries' => Country::count(),
            'active_countries' => Country::where('is_active', true)->count(),
            'inactive_countries' => Country::where('is_active', false)->count(),
            'by_region' => Country::select('region', DB::raw('count(*) as count'))
                ->whereNotNull('region')
                ->groupBy('region')
                ->orderBy('count', 'desc')
                ->get(),
            'by_currency' => Country::select('currency_code', 'currency_name', DB::raw('count(*) as count'))
                ->whereNotNull('currency_code')
                ->groupBy('currency_code', 'currency_name')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get(),
            'users_by_country' => Country::select('countries.*', DB::raw('count(accounts.id) as user_count'))
                ->leftJoin('accounts', 'countries.id', '=', 'accounts.country_id')
                ->groupBy('countries.id')
                ->orderBy('user_count', 'desc')
                ->limit(20)
                ->get(),
        ];

        return view($this->viewPath . '.statistics', compact('stats'));
    }

    /**
     * Export countries to CSV
     */
    public function export()
    {
        $countries = Country::ordered()->get();
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="countries_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function() use ($countries) {
            $file = fopen('php://output', 'w');
            
            // Add header row
            fputcsv($file, [
                'Code', 'Code3', 'Name', 'Native Name', 'Capital', 
                'Region', 'Currency Code', 'Currency Name', 'Phone Code',
                'Languages', 'Timezone', 'Active', 'Display Order'
            ]);
            
            foreach ($countries as $country) {
                fputcsv($file, [
                    $country->code,
                    $country->code3,
                    $country->name,
                    $country->native_name,
                    $country->capital,
                    $country->region,
                    $country->currency_code,
                    $country->currency_name,
                    $country->phone_code,
                    is_array($country->languages) ? implode(',', $country->languages) : $country->languages,
                    $country->timezone,
                    $country->is_active ? 'Yes' : 'No',
                    $country->display_order,
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}