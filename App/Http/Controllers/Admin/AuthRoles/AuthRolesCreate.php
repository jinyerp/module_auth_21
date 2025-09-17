<?php

namespace Jiny\Auth\App\Http\Controllers\Admin\AuthRoles;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuthRolesCreate extends Controller
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
            $jsonData['template']['create'] = 'jiny-admin::crud.create';
            $jsonData['template']['form'] = 'jiny-auth::admin.auth_roles.create';
            
            return $jsonData;
        }
        return [];
    }

    public function index(Request $request)
    {
        return view($this->jsonData['template']['create'], [
            'jsonData' => $this->jsonData
        ]);
    }

    /**
     * Hook: 생성 폼 초기화
     */
    public function hookCreating($wire, $value)
    {
        // 기본값 설정
        if (!isset($value['is_active'])) {
            $value['is_active'] = true;
        }
        
        if (!isset($value['permissions'])) {
            $value['permissions'] = [];
        }
        
        return $value;
    }

    /**
     * Hook: 폼 필드 커스터마이징
     */
    public function hookFormFields($wire)
    {
        return [
            'name' => [
                'label' => '역할명',
                'type' => 'text',
                'required' => true,
                'placeholder' => '예: 관리자, 편집자, 사용자'
            ],
            'slug' => [
                'label' => '슬러그',
                'type' => 'text',
                'required' => true,
                'placeholder' => '예: admin, editor, user',
                'hint' => 'URL에 사용되는 고유 식별자입니다.'
            ],
            'description' => [
                'label' => '설명',
                'type' => 'textarea',
                'rows' => 3,
                'placeholder' => '역할에 대한 설명을 입력하세요.'
            ],
            'permissions' => [
                'label' => '권한',
                'type' => 'json',
                'hint' => 'JSON 형식으로 권한을 정의합니다.',
                'placeholder' => '{"read": true, "write": false}'
            ],
            'is_active' => [
                'label' => '활성화',
                'type' => 'checkbox',
                'default' => true
            ]
        ];
    }

    /**
     * Hook: 유효성 검사
     */
    public function hookValidating($wire, $form)
    {
        // 커스텀 유효성 검사
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
        
        // 중복 검사
        $exists = \DB::table('accounts_roles')
            ->where('slug', $form['slug'])
            ->exists();
        
        if ($exists) {
            return "이미 사용중인 슬러그입니다: {$form['slug']}";
        }
        
        return $form;
    }

    /**
     * Hook: 저장 전 처리
     */
    public function hookStoring($wire, $form)
    {
        // 슬러그 자동 생성 (비어있는 경우)
        if (empty($form['slug']) && !empty($form['name'])) {
            $form['slug'] = Str::slug($form['name']);
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
        
        // 타임스탬프 추가
        $form['created_at'] = now();
        $form['updated_at'] = now();
        
        return $form;
    }

    /**
     * Hook: 저장 후 처리
     */
    public function hookStored($wire, $model)
    {
        // 로그 기록
        activity()
            ->performedOn($model)
            ->log("새 역할 생성: {$model->name}");
        
        // 성공 메시지
        session()->flash('message', "역할 '{$model->name}'이(가) 성공적으로 생성되었습니다.");
    }

    /**
     * Hook: 기본값 설정
     */
    public function hookDefaults($wire)
    {
        return [
            'is_active' => true,
            'permissions' => '{}'
        ];
    }
}