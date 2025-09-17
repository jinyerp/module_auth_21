<?php

namespace Jiny\Auth\App\Http\Controllers\Admin\AuthRoles;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuthRolesEdit extends Controller
{
    public $jsonData;

    public function __construct()
    {
        $this->jsonData = $this->loadJsonData();
    }

    protected function loadJsonData()
    {
        $path = __DIR__ . '/AuthRoles.json';
        if (file_exists($path)) {
            $jsonData = json_decode(file_get_contents($path), true);
            
            // controllerClass 추가
            $jsonData['controllerClass'] = self::class;
            
            // 템플릿 설정
            if (!isset($jsonData['template'])) {
                $jsonData['template'] = [];
            }
            $jsonData['template']['edit'] = 'jiny-admin::crud.edit';
            $jsonData['template']['form'] = 'jiny-auth::admin.auth_roles.edit';
            
            return $jsonData;
        }
        return [];
    }

    public function index(Request $request, $id)
    {
        return view($this->jsonData['template']['edit'], [
            'jsonData' => $this->jsonData,
            'id' => $id
        ]);
    }

    /**
     * Hook: 편집 폼 로드
     */
    public function hookEditing($wire, $model)
    {
        // permissions JSON 디코드
        if ($model && isset($model->permissions)) {
            if (is_string($model->permissions)) {
                $decoded = json_decode($model->permissions, true);
                $model->permissions = $decoded ?: [];
            }
        }
        
        return $model;
    }

    /**
     * Hook: 폼 필드 커스터마이징
     */
    public function hookFormFields($wire, $model)
    {
        return [
            'name' => [
                'label' => '역할명',
                'type' => 'text',
                'required' => true,
                'value' => $model->name ?? ''
            ],
            'slug' => [
                'label' => '슬러그',
                'type' => 'text',
                'required' => true,
                'value' => $model->slug ?? '',
                'hint' => 'URL에 사용되는 고유 식별자입니다. 변경 시 주의하세요.'
            ],
            'description' => [
                'label' => '설명',
                'type' => 'textarea',
                'rows' => 3,
                'value' => $model->description ?? ''
            ],
            'permissions' => [
                'label' => '권한',
                'type' => 'json',
                'value' => is_array($model->permissions) ? json_encode($model->permissions, JSON_PRETTY_PRINT) : $model->permissions,
                'hint' => 'JSON 형식으로 권한을 정의합니다.'
            ],
            'is_active' => [
                'label' => '활성화',
                'type' => 'checkbox',
                'value' => $model->is_active ?? false
            ]
        ];
    }

    /**
     * Hook: 유효성 검사
     */
    public function hookValidating($wire, $form)
    {
        // 필수 필드 검사
        if (empty($form['name'])) {
            return "역할명은 필수 입력 항목입니다.";
        }
        
        if (empty($form['slug'])) {
            return "슬러그는 필수 입력 항목입니다.";
        }
        
        // 슬러그 형식 검사
        if (!preg_match('/^[a-z0-9-_]+$/', $form['slug'])) {
            return "슬러그는 소문자, 숫자, 하이픈, 언더스코어만 사용할 수 있습니다.";
        }
        
        // 중복 검사 (자기 자신 제외)
        if (isset($wire->modelId)) {
            $exists = \DB::table('accounts_roles')
                ->where('slug', $form['slug'])
                ->where('id', '!=', $wire->modelId)
                ->exists();
            
            if ($exists) {
                return "이미 사용중인 슬러그입니다: {$form['slug']}";
            }
        }
        
        return $form;
    }

    /**
     * Hook: 업데이트 전 처리
     */
    public function hookUpdating($wire, $form)
    {
        // 시스템 역할 보호
        if (isset($wire->model) && $this->isSystemRole($wire->model)) {
            if ($form['slug'] !== $wire->model->slug) {
                return "시스템 역할의 슬러그는 변경할 수 없습니다.";
            }
        }
        
        // permissions JSON 처리
        if (isset($form['permissions'])) {
            if (is_array($form['permissions'])) {
                $form['permissions'] = json_encode($form['permissions']);
            } elseif (is_string($form['permissions']) && !empty($form['permissions'])) {
                // JSON 유효성 검사
                $decoded = json_decode($form['permissions']);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return "권한 필드의 JSON 형식이 올바르지 않습니다.";
                }
            } else {
                $form['permissions'] = '{}';
            }
        }
        
        // is_active 값 처리
        $form['is_active'] = isset($form['is_active']) && $form['is_active'] ? 1 : 0;
        
        // 타임스탬프 업데이트
        $form['updated_at'] = now();
        
        return $form;
    }

    /**
     * Hook: 업데이트 후 처리
     */
    public function hookUpdated($wire, $model)
    {
        // 캐시 클리어 (필요한 경우)
        // Cache::forget('roles');
        
        // 로그 기록
        activity()
            ->performedOn($model)
            ->log("역할 수정: {$model->name}");
        
        // 성공 메시지
        session()->flash('message', "역할 '{$model->name}'이(가) 성공적으로 수정되었습니다.");
    }

    /**
     * 시스템 역할 확인
     */
    protected function isSystemRole($model)
    {
        $systemRoles = ['super-admin', 'admin', 'user'];
        return in_array($model->slug, $systemRoles);
    }
}