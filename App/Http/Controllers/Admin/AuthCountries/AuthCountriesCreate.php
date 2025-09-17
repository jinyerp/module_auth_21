<?php

namespace Jiny\Auth\App\Http\Controllers\Admin\AuthCountries;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\App\Models\Country;

class AuthCountriesCreate extends Controller
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

    public function index()
    {
        $this->jsonData['controllerClass'] = self::class;
        
        return view('jiny-admin::crud.create', [
            'jsonData' => $this->jsonData,
            'viewPath' => $this->viewPath,
        ]);
    }

    /**
     * Hook called when creating form is initialized
     */
    public function hookCreating($wire, $value)
    {
        // Set default values
        $value['is_active'] = true;
        $value['display_order'] = 999;
        
        // Load regions for dropdown
        $wire->regions = [
            'Africa' => 'Africa',
            'Americas' => 'Americas', 
            'Asia' => 'Asia',
            'Europe' => 'Europe',
            'Oceania' => 'Oceania',
            'Antarctic' => 'Antarctic'
        ];
        
        return $value;
    }

    /**
     * Hook for validating data before saving
     */
    public function hookValidating($wire, $form)
    {
        // Check for duplicate ISO codes
        $existingByCode = Country::where('code', strtoupper($form['code']))->first();
        if ($existingByCode) {
            return "ISO2 코드 '{$form['code']}'는 이미 사용 중입니다.";
        }
        
        if (!empty($form['code3'])) {
            $existingByCode3 = Country::where('code3', strtoupper($form['code3']))->first();
            if ($existingByCode3) {
                return "ISO3 코드 '{$form['code3']}'는 이미 사용 중입니다.";
            }
        }
        
        // Validate phone code format
        if (!empty($form['phone_code'])) {
            if (!preg_match('/^\d{1,4}$/', $form['phone_code'])) {
                return "국가번호는 1-4자리 숫자여야 합니다.";
            }
        }
        
        return $form;
    }

    /**
     * Hook called before storing data
     */
    public function hookStoring($wire, $form)
    {
        // Convert ISO codes to uppercase
        if (isset($form['code'])) {
            $form['code'] = strtoupper(trim($form['code']));
        }
        if (isset($form['code3'])) {
            $form['code3'] = strtoupper(trim($form['code3']));
        }
        if (isset($form['currency_code'])) {
            $form['currency_code'] = strtoupper(trim($form['currency_code']));
        }
        
        // Process languages
        if (!empty($form['languages']) && is_string($form['languages'])) {
            $form['languages'] = array_map('trim', explode(',', $form['languages']));
        }
        
        // Process timezones
        if (!empty($form['timezones']) && is_string($form['timezones'])) {
            $form['timezones'] = array_map('trim', explode(',', $form['timezones']));
        }
        
        // Set display order for popular countries
        $popularCountries = [
            'KR' => 1,  // Korea
            'US' => 2,  // United States
            'JP' => 3,  // Japan
            'CN' => 4,  // China
            'GB' => 5,  // United Kingdom
            'DE' => 6,  // Germany
            'FR' => 7,  // France
            'CA' => 8,  // Canada
            'AU' => 9,  // Australia
        ];
        
        if (isset($form['code']) && isset($popularCountries[$form['code']])) {
            $form['display_order'] = $popularCountries[$form['code']];
        }
        
        // Clean phone code (remove + if present)
        if (!empty($form['phone_code'])) {
            $form['phone_code'] = ltrim($form['phone_code'], '+');
        }
        
        // Process metadata
        if (isset($form['meta']) && is_string($form['meta'])) {
            try {
                $form['meta'] = json_decode($form['meta'], true);
            } catch (\Exception $e) {
                unset($form['meta']);
            }
        }
        
        return $form;
    }

    /**
     * Hook called after successful storage
     */
    public function hookStored($wire, $model)
    {
        // Log the creation
        activity()
            ->performedOn($model)
            ->log("국가 '{$model->name}' (코드: {$model->code})가 추가되었습니다");
            
        // Clear any caches if needed
        cache()->forget('countries_list');
        cache()->forget('countries_active');
    }

    /**
     * Hook for customizing form fields
     */
    public function hookFormFields($wire)
    {
        return [
            'code' => [
                'label' => 'ISO2 코드',
                'type' => 'text',
                'required' => true,
                'maxlength' => 2,
                'placeholder' => 'KR',
                'hint' => '2자리 ISO 국가 코드 (예: KR, US, JP)',
            ],
            'code3' => [
                'label' => 'ISO3 코드',
                'type' => 'text',
                'maxlength' => 3,
                'placeholder' => 'KOR',
                'hint' => '3자리 ISO 국가 코드 (예: KOR, USA, JPN)',
            ],
            'name' => [
                'label' => '국가명',
                'type' => 'text',
                'required' => true,
                'placeholder' => '대한민국',
            ],
            'native_name' => [
                'label' => '현지 국가명',
                'type' => 'text',
                'placeholder' => '대한민국',
                'hint' => '현지 언어로 표기된 국가명',
            ],
            'capital' => [
                'label' => '수도',
                'type' => 'text',
                'placeholder' => '서울',
            ],
            'region' => [
                'label' => '대륙',
                'type' => 'select',
                'options' => [
                    'Africa' => 'Africa',
                    'Americas' => 'Americas',
                    'Asia' => 'Asia',
                    'Europe' => 'Europe',
                    'Oceania' => 'Oceania',
                    'Antarctic' => 'Antarctic',
                ],
            ],
            'subregion' => [
                'label' => '하위 지역',
                'type' => 'text',
                'placeholder' => 'Eastern Asia',
            ],
            'currency_code' => [
                'label' => '통화 코드',
                'type' => 'text',
                'maxlength' => 3,
                'placeholder' => 'KRW',
                'hint' => '3자리 ISO 통화 코드',
            ],
            'currency_name' => [
                'label' => '통화명',
                'type' => 'text',
                'placeholder' => '원',
            ],
            'currency_symbol' => [
                'label' => '통화 기호',
                'type' => 'text',
                'placeholder' => '₩',
            ],
            'phone_code' => [
                'label' => '국가번호',
                'type' => 'text',
                'placeholder' => '82',
                'hint' => '+ 없이 숫자만 입력',
            ],
            'languages' => [
                'label' => '언어',
                'type' => 'text',
                'placeholder' => 'ko, en',
                'hint' => '쉼표로 구분하여 입력',
            ],
            'timezone' => [
                'label' => '주요 시간대',
                'type' => 'text',
                'placeholder' => 'Asia/Seoul',
            ],
            'timezones' => [
                'label' => '모든 시간대',
                'type' => 'text',
                'placeholder' => 'Asia/Seoul',
                'hint' => '여러 시간대가 있는 경우 쉼표로 구분',
            ],
            'flag_emoji' => [
                'label' => '국기 이모지',
                'type' => 'text',
                'placeholder' => '🇰🇷',
            ],
            'display_order' => [
                'label' => '표시 순서',
                'type' => 'number',
                'default' => 999,
                'hint' => '낮은 숫자가 먼저 표시됨 (주요 국가는 1-10)',
            ],
            'is_active' => [
                'label' => '활성화',
                'type' => 'checkbox',
                'default' => true,
            ],
        ];
    }
}