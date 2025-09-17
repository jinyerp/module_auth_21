<?php
namespace Jiny\Auth\App\Http\Controllers\Admin\AuthGrades;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\App\Models\Grade;
use Illuminate\Support\Facades\DB;

/**
 * 회원 등급 수정 컨트롤러
 */
class AuthGradesEdit extends Controller
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
     * 회원 등급 수정 페이지
     */
    public function index(Request $request, $id)
    {
        $grade = Grade::findOrFail($id);
        
        // Hook 시스템을 위한 controllerClass 설정
        $this->jsonData['controllerClass'] = static::class;
        
        // 템플릿 경로 설정
        $this->jsonData['template'] = [
            'layout' => 'jiny-admin::crud.edit',
            'form' => $this->baseViewPath . '.edit'
        ];

        // 라우트 정보 설정
        $this->jsonData['routes'] = [
            'update' => route('admin.auth.grades.edit', $id),
            'cancel' => route('admin.auth.grades')
        ];

        return view('jiny-admin::crud.edit', [
            'jsonData' => $this->jsonData,
            'data' => $grade,
            'id' => $id,
            'controllerClass' => static::class
        ]);
    }

    /**
     * Hook: 편집 폼 로드 시
     */
    public function hookEditing($livewire, $model)
    {
        // 혜택 정보 디코딩
        if ($model && isset($model->benefits)) {
            if (is_string($model->benefits)) {
                $model->benefits = json_decode($model->benefits, true);
            }
        }
        
        // 관련 통계 정보 추가
        if ($model) {
            $model->user_count = DB::table('accounts')
                ->where('grade_id', $model->id)
                ->count();
        }
        
        return $model;
    }

    /**
     * Hook: 업데이트 전 유효성 검사
     */
    public function hookValidating($livewire, $form)
    {
        $errors = [];
        $id = $livewire->modelId ?? null;
        
        // 등급명 중복 검사 (자신 제외)
        $nameExists = Grade::where('name', $form['name'])
            ->where('id', '!=', $id)
            ->exists();
        if ($nameExists) {
            $errors['name'] = '이미 사용중인 등급명입니다.';
        }
        
        // 등급 코드 중복 검사 (자신 제외)
        $codeExists = Grade::where('code', $form['code'])
            ->where('id', '!=', $id)
            ->exists();
        if ($codeExists) {
            $errors['code'] = '이미 사용중인 등급 코드입니다.';
        }
        
        // 레벨 중복 검사 (자신 제외)
        if (isset($form['level'])) {
            $levelExists = Grade::where('level', $form['level'])
                ->where('id', '!=', $id)
                ->exists();
            if ($levelExists) {
                $errors['level'] = '이미 사용중인 레벨입니다.';
            }
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
     * Hook: 업데이트 전 데이터 처리
     */
    public function hookUpdating($livewire, $form)
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
        
        // 등급 변경 시 사용자 알림 플래그
        $originalGrade = Grade::find($livewire->modelId);
        if ($originalGrade && $originalGrade->level != $form['level']) {
            $form['_level_changed'] = true;
        }
        
        return $form;
    }

    /**
     * Hook: 업데이트 후 처리
     */
    public function hookUpdated($livewire, $model)
    {
        // 로그 기록
        activity()
            ->performedOn($model)
            ->causedBy(auth()->user())
            ->log('회원 등급 수정: ' . $model->name);
        
        // 등급 레벨이 변경된 경우 관련 사용자 처리
        if (isset($model->_level_changed) && $model->_level_changed) {
            $this->handleLevelChange($model);
        }
        
        // 성공 메시지 설정
        session()->flash('message', '회원 등급이 성공적으로 수정되었습니다.');
    }

    /**
     * Hook: 폼 필드 커스터마이징
     */
    public function hookFormFields($livewire, $model)
    {
        $fields = $this->jsonData['fields'] ?? [];
        
        // 사용자가 있는 등급의 경우 레벨 변경 주의 메시지 추가
        if ($model && $model->user_count > 0) {
            if (isset($fields['level'])) {
                $fields['level']['help'] = "주의: 이 등급을 사용중인 {$model->user_count}명의 회원이 있습니다.";
            }
        }
        
        return $fields;
    }

    /**
     * 등급 레벨 변경 처리
     */
    private function handleLevelChange($grade)
    {
        // 등급 레벨 변경 시 관련 사용자에게 알림 발송 등의 처리
        $affectedUsers = DB::table('accounts')
            ->where('grade_id', $grade->id)
            ->count();
        
        if ($affectedUsers > 0) {
            // 알림 발송 로직 (필요시 구현)
            \Log::info("등급 레벨 변경 영향: {$grade->name} - {$affectedUsers}명의 사용자");
        }
    }
}