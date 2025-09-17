<?php

namespace Jiny\Auth\App\Http\Controllers\Admin\AuthCountries;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\App\Models\Country;

class AuthCountriesEdit extends Controller
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
        
        return view('jiny-admin::crud.edit', [
            'jsonData' => $this->jsonData,
            'viewPath' => $this->viewPath,
            'data' => $country,
            'id' => $id,
        ]);
    }

    /**
     * Hook called when edit form is loaded
     */
    public function hookEditing($wire, $model)
    {
        // Load regions for dropdown
        $wire->regions = [
            'Africa' => 'Africa',
            'Americas' => 'Americas',
            'Asia' => 'Asia', 
            'Europe' => 'Europe',
            'Oceania' => 'Oceania',
            'Antarctic' => 'Antarctic'
        ];
        
        // Convert arrays to string for form display
        if (is_array($model->languages)) {
            $model->languages_string = implode(', ', $model->languages);
        }
        
        if (is_array($model->timezones)) {
            $model->timezones_string = implode(', ', $model->timezones);
        }
        
        // Get usage statistics
        $model->user_count = \DB::table('accounts')
            ->where('country_id', $model->id)
            ->count();
        
        return $model;
    }

    /**
     * Hook for validating data before updating
     */
    public function hookValidating($wire, $form)
    {
        // Get the current country
        $currentCountry = Country::find($wire->modelId);
        
        // Check for duplicate ISO codes (excluding current record)
        if (isset($form['code'])) {
            $existingByCode = Country::where('code', strtoupper($form['code']))
                ->where('id', '!=', $currentCountry->id)
                ->first();
            if ($existingByCode) {
                return "ISO2 코드 '{$form['code']}'는 이미 사용 중입니다.";
            }
        }
        
        if (!empty($form['code3'])) {
            $existingByCode3 = Country::where('code3', strtoupper($form['code3']))
                ->where('id', '!=', $currentCountry->id)
                ->first();
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
        
        // Check if trying to deactivate a country with active users
        if (isset($form['is_active']) && !$form['is_active']) {
            $userCount = \DB::table('accounts')
                ->where('country_id', $currentCountry->id)
                ->where('is_active', true)
                ->count();
            
            if ($userCount > 0) {
                return "활성 사용자가 {$userCount}명 있는 국가는 비활성화할 수 없습니다.";
            }
        }
        
        return $form;
    }

    /**
     * Hook called before updating data
     */
    public function hookUpdating($wire, $form)
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
        if (isset($form['languages']) && is_string($form['languages'])) {
            $form['languages'] = array_map('trim', explode(',', $form['languages']));
        }
        
        // Process timezones
        if (isset($form['timezones']) && is_string($form['timezones'])) {
            $form['timezones'] = array_map('trim', explode(',', $form['timezones']));
        }
        
        // Clean phone code
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
        
        // Remove processed fields
        unset($form['languages_string']);
        unset($form['timezones_string']);
        unset($form['user_count']);
        
        return $form;
    }

    /**
     * Hook called after successful update
     */
    public function hookUpdated($wire, $model)
    {
        // Get the changes
        $changes = $model->getChanges();
        unset($changes['updated_at']);
        
        if (!empty($changes)) {
            // Log the update with changes
            $changeLog = [];
            foreach ($changes as $field => $newValue) {
                $oldValue = $model->getOriginal($field);
                $changeLog[] = "{$field}: '{$oldValue}' → '{$newValue}'";
            }
            
            activity()
                ->performedOn($model)
                ->withProperties(['changes' => $changes])
                ->log("국가 '{$model->name}' (코드: {$model->code})가 수정되었습니다: " . implode(', ', $changeLog));
        }
        
        // Clear caches
        cache()->forget('countries_list');
        cache()->forget('countries_active');
        cache()->forget("country_{$model->id}");
        cache()->forget("country_{$model->code}");
    }

    /**
     * Hook for customizing form fields
     */
    public function hookFormFields($wire, $model)
    {
        $fields = [
            'code' => [
                'label' => 'ISO2 코드',
                'type' => 'text',
                'required' => true,
                'maxlength' => 2,
                'value' => $model->code,
                'hint' => '2자리 ISO 국가 코드',
            ],
            'code3' => [
                'label' => 'ISO3 코드',
                'type' => 'text',
                'maxlength' => 3,
                'value' => $model->code3,
                'hint' => '3자리 ISO 국가 코드',
            ],
            'name' => [
                'label' => '국가명',
                'type' => 'text',
                'required' => true,
                'value' => $model->name,
            ],
            'native_name' => [
                'label' => '현지 국가명',
                'type' => 'text',
                'value' => $model->native_name,
                'hint' => '현지 언어로 표기된 국가명',
            ],
            'capital' => [
                'label' => '수도',
                'type' => 'text',
                'value' => $model->capital,
            ],
            'region' => [
                'label' => '대륙',
                'type' => 'select',
                'value' => $model->region,
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
                'value' => $model->subregion,
            ],
            'currency_code' => [
                'label' => '통화 코드',
                'type' => 'text',
                'maxlength' => 3,
                'value' => $model->currency_code,
                'hint' => '3자리 ISO 통화 코드',
            ],
            'currency_name' => [
                'label' => '통화명',
                'type' => 'text',
                'value' => $model->currency_name,
            ],
            'currency_symbol' => [
                'label' => '통화 기호',
                'type' => 'text',
                'value' => $model->currency_symbol,
            ],
            'phone_code' => [
                'label' => '국가번호',
                'type' => 'text',
                'value' => $model->phone_code,
                'hint' => '+ 없이 숫자만 입력',
            ],
            'languages' => [
                'label' => '언어',
                'type' => 'text',
                'value' => is_array($model->languages) ? implode(', ', $model->languages) : $model->languages,
                'hint' => '쉼표로 구분하여 입력',
            ],
            'timezone' => [
                'label' => '주요 시간대',
                'type' => 'text',
                'value' => $model->timezone,
            ],
            'timezones' => [
                'label' => '모든 시간대',
                'type' => 'text',
                'value' => is_array($model->timezones) ? implode(', ', $model->timezones) : $model->timezones,
                'hint' => '여러 시간대가 있는 경우 쉼표로 구분',
            ],
            'flag_emoji' => [
                'label' => '국기 이모지',
                'type' => 'text',
                'value' => $model->flag_emoji,
            ],
            'display_order' => [
                'label' => '표시 순서',
                'type' => 'number',
                'value' => $model->display_order,
                'hint' => '낮은 숫자가 먼저 표시됨',
            ],
            'is_active' => [
                'label' => '활성화',
                'type' => 'checkbox',
                'value' => $model->is_active,
                'disabled' => $model->user_count > 0,
                'hint' => $model->user_count > 0 ? "사용자 {$model->user_count}명이 이 국가를 사용 중입니다" : null,
            ],
        ];
        
        return $fields;
    }
}