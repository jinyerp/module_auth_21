<?php
namespace Jiny\Auth\App\Http\Controllers\Admin\AuthGrades;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\App\Models\Grade;

/**
 * 회원 등급 생성 컨트롤러
 */
class AuthGradesCreate extends Controller
{
    private $baseViewPath = 'jiny-auth::admin.auth_grades';
    private $jsonData;

    public function __construct()
    {
        $this->jsonData = $this->loadJsonData();
    }

    /**
     * JSON 설정 파일 로드
     */
    private function loadJsonData()
    {
        $jsonPath = __DIR__ . '/AuthGrades.json';
        if (file_exists($jsonPath)) {
            $jsonContent = file_get_contents($jsonPath);
            return json_decode($jsonContent, true);
        }
        
        return [];
    }

    /**
     * 회원 등급 생성 페이지
     */
    public function index(Request $request)
    {
        // Hook 시스템을 위한 controllerClass 설정
        $this->jsonData['controllerClass'] = static::class;
        
        // 템플릿 경로 설정
        $this->jsonData['template'] = [
            'layout' => 'jiny-admin::crud.create',
            'form' => $this->baseViewPath . '.create'
        ];

        // 라우트 정보 설정
        $this->jsonData['routes'] = [
            'store' => route('admin.auth.grades'),
            'cancel' => route('admin.auth.grades')
        ];

        return view('jiny-admin::crud.create', [
            'jsonData' => $this->jsonData,
            'controllerClass' => static::class
        ]);
    }

    /**
     * Hook: 생성 폼 초기화
     */
    public function hookCreating($livewire, $value)
    {
        // 기본값 설정
        if (!isset($value['is_active'])) {
            $value['is_active'] = true;
        }
        
        if (!isset($value['point_rate'])) {
            $value['point_rate'] = 0.01; // 기본 1% 적립
        }
        
        if (!isset($value['discount_rate'])) {
            $value['discount_rate'] = 0;
        }
        
        if (!isset($value['min_purchase'])) {
            $value['min_purchase'] = 0;
        }
        
        // 다음 레벨 자동 계산
        if (!isset($value['level'])) {
            $maxLevel = Grade::max('level') ?? 0;
            $value['level'] = $maxLevel + 1;
        }
        
        return $value;
    }

    /**
     * Hook: 저장 전 유효성 검사
     */
    public function hookValidating($livewire, $form)
    {
        $errors = [];
        
        // 등급명 중복 검사
        if (Grade::where('name', $form['name'])->exists()) {
            $errors['name'] = '이미 사용중인 등급명입니다.';
        }
        
        // 등급 코드 중복 검사
        if (Grade::where('code', $form['code'])->exists()) {
            $errors['code'] = '이미 사용중인 등급 코드입니다.';
        }
        
        // 레벨 중복 검사
        if (isset($form['level']) && Grade::where('level', $form['level'])->exists()) {
            $errors['level'] = '이미 사용중인 레벨입니다.';
        }
        
        // 포인트 적립률 범위 검사
        if (isset($form['point_rate'])) {
            if ($form['point_rate'] < 0 || $form['point_rate'] > 1) {
                $errors['point_rate'] = '포인트 적립률은 0과 1 사이의 값이어야 합니다.';
            }
        }
        
        // 할인율 범위 검사
        if (isset($form['discount_rate'])) {
            if ($form['discount_rate'] < 0 || $form['discount_rate'] > 100) {
                $errors['discount_rate'] = '할인율은 0과 100 사이의 값이어야 합니다.';
            }
        }
        
        if (!empty($errors)) {
            foreach ($errors as $field => $message) {
                $livewire->addError('form.' . $field, $message);
            }
            return false;
        }
        
        return true;
    }

    /**
     * Hook: 데이터 저장 전 처리
     */
    public function hookStoring($livewire, $form)
    {
        // 혜택 정보를 JSON으로 변환
        if (isset($form['benefits']) && is_array($form['benefits'])) {
            $form['benefits'] = json_encode($form['benefits']);
        }
        
        // 색상 코드 정규화
        if (isset($form['color']) && !empty($form['color'])) {
            if (!str_starts_with($form['color'], '#')) {
                $form['color'] = '#' . $form['color'];
            }
        }
        
        // 최소 구매금액 기본값
        if (!isset($form['min_purchase']) || empty($form['min_purchase'])) {
            $form['min_purchase'] = 0;
        }
        
        return $form;
    }

    /**
     * Hook: 저장 후 처리
     */
    public function hookStored($livewire, $model)
    {
        // 로그 기록
        activity()
            ->performedOn($model)
            ->causedBy(auth()->user())
            ->log('회원 등급 생성: ' . $model->name);
        
        // 성공 메시지 설정
        session()->flash('message', '회원 등급이 성공적으로 생성되었습니다.');
    }

    /**
     * Hook: 폼 필드 커스터마이징
     */
    public function hookFormFields($livewire)
    {
        return $this->jsonData['fields'] ?? [];
    }

    /**
     * Hook: 기본값 설정
     */
    public function hookDefaults($livewire)
    {
        return [
            'is_active' => true,
            'point_rate' => 0.01,
            'discount_rate' => 0,
            'min_purchase' => 0
        ];
    }
}