<?php

namespace Jiny\Auth\App\Http\Controllers\Admin\AuthCountries;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\App\Models\Country;

class AuthCountriesDelete extends Controller
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
        
        // Check if country can be deleted
        $canDelete = $this->hookCanDelete(null, $country);
        
        return view('jiny-admin::crud.delete', [
            'jsonData' => $this->jsonData,
            'data' => $country,
            'id' => $id,
            'canDelete' => $canDelete,
            'deleteMessage' => $this->getDeleteMessage($country),
        ]);
    }

    /**
     * Get delete confirmation message
     */
    private function getDeleteMessage($country)
    {
        $userCount = \DB::table('accounts')
            ->where('country_id', $country->id)
            ->count();
        
        if ($userCount > 0) {
            return "경고: 이 국가('{$country->name}')는 {$userCount}명의 사용자가 사용 중입니다. 삭제할 수 없습니다.";
        }
        
        return "정말로 국가 '{$country->name}' (코드: {$country->code})를 삭제하시겠습니까? 이 작업은 되돌릴 수 없습니다.";
    }

    /**
     * Hook to check if country can be deleted
     */
    public function hookCanDelete($wire, $model)
    {
        // Check if there are any users from this country
        $userCount = \DB::table('accounts')
            ->where('country_id', $model->id)
            ->count();
        
        if ($userCount > 0) {
            if ($wire) {
                $wire->addError('delete', "이 국가는 {$userCount}명의 사용자가 사용 중이므로 삭제할 수 없습니다.");
            }
            return false;
        }
        
        // Check if this is a protected/popular country
        $protectedCountries = ['KR', 'US', 'JP', 'CN', 'GB', 'DE', 'FR', 'CA', 'AU'];
        if (in_array($model->code, $protectedCountries)) {
            if ($wire) {
                $wire->addError('delete', "주요 국가는 보호되어 있어 삭제할 수 없습니다. 대신 비활성화하세요.");
            }
            return false;
        }
        
        return true;
    }

    /**
     * Hook called before deleting
     */
    public function hookDeleting($wire, $id)
    {
        $country = Country::find($id);
        
        if (!$country) {
            return "국가를 찾을 수 없습니다.";
        }
        
        // Final check if can delete
        if (!$this->hookCanDelete($wire, $country)) {
            return false;
        }
        
        // Store country info for logging
        $wire->deletingCountry = [
            'name' => $country->name,
            'code' => $country->code,
            'code3' => $country->code3,
        ];
        
        return true;
    }

    /**
     * Hook called after successful deletion
     */
    public function hookDeleted($wire, $id)
    {
        // Log the deletion
        if (isset($wire->deletingCountry)) {
            activity()
                ->withProperties($wire->deletingCountry)
                ->log("국가 '{$wire->deletingCountry['name']}' (코드: {$wire->deletingCountry['code']})가 삭제되었습니다");
        }
        
        // Clear caches
        cache()->forget('countries_list');
        cache()->forget('countries_active');
        cache()->forget("country_{$id}");
        
        // Redirect with success message
        session()->flash('success', '국가가 성공적으로 삭제되었습니다.');
    }

    /**
     * Hook for soft deleting (deactivating instead of deleting)
     */
    public function hookSoftDeleting($wire, $model)
    {
        // Instead of deleting, deactivate the country
        $model->is_active = false;
        $model->save();
        
        activity()
            ->performedOn($model)
            ->log("국가 '{$model->name}' (코드: {$model->code})가 비활성화되었습니다");
        
        session()->flash('info', '국가가 비활성화되었습니다. 데이터는 보존됩니다.');
        
        return false; // Prevent actual deletion
    }

    /**
     * Bulk delete countries
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:countries,id',
        ]);

        $deleted = 0;
        $failed = [];
        
        foreach ($request->ids as $id) {
            $country = Country::find($id);
            
            if ($this->hookCanDelete(null, $country)) {
                $country->delete();
                $deleted++;
            } else {
                $failed[] = $country->name;
            }
        }

        $message = "{$deleted}개 국가가 삭제되었습니다.";
        
        if (!empty($failed)) {
            $message .= " 다음 국가는 삭제할 수 없습니다: " . implode(', ', $failed);
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'deleted' => $deleted,
            'failed' => $failed,
        ]);
    }
}