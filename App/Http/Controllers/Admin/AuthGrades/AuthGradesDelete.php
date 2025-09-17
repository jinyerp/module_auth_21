<?php
namespace Jiny\Auth\App\Http\Controllers\Admin\AuthGrades;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\App\Models\Grade;
use Illuminate\Support\Facades\DB;

/**
 * 회원 등급 삭제 컨트롤러
 */
class AuthGradesDelete extends Controller
{
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
     * 회원 등급 삭제 확인 페이지
     */
    public function index(Request $request, $id)
    {
        $grade = Grade::findOrFail($id);
        
        // Hook 시스템을 위한 controllerClass 설정
        $this->jsonData['controllerClass'] = static::class;
        
        // 템플릿 경로 설정
        $this->jsonData['template'] = [
            'layout' => 'jiny-admin::crud.delete'
        ];

        // 라우트 정보 설정
        $this->jsonData['routes'] = [
            'destroy' => route('admin.auth.grades.delete', $id),
            'cancel' => route('admin.auth.grades')
        ];

        // 삭제 확인 메시지
        $userCount = DB::table('accounts')->where('grade_id', $id)->count();
        $warningMessage = null;
        
        if ($userCount > 0) {
            $warningMessage = "주의: 이 등급을 사용중인 {$userCount}명의 회원이 있습니다. 삭제 시 이 회원들의 등급이 초기화됩니다.";
        }

        return view('jiny-admin::crud.delete', [
            'jsonData' => $this->jsonData,
            'data' => $grade,
            'id' => $id,
            'title' => '회원 등급 삭제',
            'message' => "'{$grade->name}' 등급을 삭제하시겠습니까?",
            'warningMessage' => $warningMessage,
            'controllerClass' => static::class
        ]);
    }

    /**
     * Hook: 삭제 가능 여부 확인
     */
    public function hookCanDelete($livewire, $model)
    {
        // 기본 등급(레벨 1)은 삭제 불가
        if ($model->level == 1) {
            $livewire->addError('delete', '기본 등급은 삭제할 수 없습니다.');
            return false;
        }
        
        // 활성화된 유일한 등급인 경우 삭제 불가
        $activeGradesCount = Grade::where('is_active', true)->count();
        if ($model->is_active && $activeGradesCount <= 1) {
            $livewire->addError('delete', '최소 하나 이상의 활성 등급이 필요합니다.');
            return false;
        }
        
        return true;
    }

    /**
     * Hook: 삭제 전 처리
     */
    public function hookDeleting($livewire, $id)
    {
        $grade = Grade::find($id);
        
        if (!$grade) {
            return "등급을 찾을 수 없습니다.";
        }
        
        // 삭제 가능 여부 재확인
        if (!$this->hookCanDelete($livewire, $grade)) {
            return false;
        }
        
        // 이 등급을 사용중인 회원들의 등급을 기본 등급으로 변경
        $affectedUsers = DB::table('accounts')
            ->where('grade_id', $id)
            ->count();
        
        if ($affectedUsers > 0) {
            // 기본 등급(레벨 1) 찾기
            $defaultGrade = Grade::where('level', 1)->first();
            
            if ($defaultGrade) {
                DB::table('accounts')
                    ->where('grade_id', $id)
                    ->update([
                        'grade_id' => $defaultGrade->id,
                        'updated_at' => now()
                    ]);
                
                \Log::info("등급 삭제로 인한 회원 등급 변경: {$affectedUsers}명이 기본 등급으로 변경됨");
            }
        }
        
        // 삭제 전 정보 백업
        $livewire->deletedGradeInfo = [
            'name' => $grade->name,
            'level' => $grade->level,
            'affected_users' => $affectedUsers
        ];
        
        return true;
    }

    /**
     * Hook: 삭제 후 처리
     */
    public function hookDeleted($livewire, $id)
    {
        // 로그 기록
        $deletedInfo = $livewire->deletedGradeInfo ?? [];
        
        activity()
            ->causedBy(auth()->user())
            ->withProperties($deletedInfo)
            ->log('회원 등급 삭제: ' . ($deletedInfo['name'] ?? 'Unknown'));
        
        // 레벨 재정렬 (선택적)
        $this->reorderLevels();
        
        // 성공 메시지 설정
        $message = '회원 등급이 성공적으로 삭제되었습니다.';
        if (isset($deletedInfo['affected_users']) && $deletedInfo['affected_users'] > 0) {
            $message .= " {$deletedInfo['affected_users']}명의 회원이 기본 등급으로 변경되었습니다.";
        }
        
        session()->flash('message', $message);
    }

    /**
     * Hook: 소프트 삭제 처리 (필요 시)
     */
    public function hookSoftDeleting($livewire, $model)
    {
        // 소프트 삭제를 사용하는 경우의 처리
        // Grade 모델에 SoftDeletes trait가 있는 경우 활용
        $model->deleted_by = auth()->id();
        $model->deleted_reason = $livewire->deleteReason ?? null;
        $model->save();
        
        return true;
    }

    /**
     * Hook: 복원 처리 (소프트 삭제된 경우)
     */
    public function hookRestoring($livewire, $model)
    {
        // 복원 시 레벨 중복 확인
        if (Grade::where('level', $model->level)->exists()) {
            // 새로운 레벨 할당
            $maxLevel = Grade::max('level') ?? 0;
            $model->level = $maxLevel + 1;
        }
        
        $model->restored_by = auth()->id();
        $model->restored_at = now();
        
        return true;
    }

    /**
     * 등급 레벨 재정렬
     */
    private function reorderLevels()
    {
        $grades = Grade::orderBy('level')->get();
        $newLevel = 1;
        
        foreach ($grades as $grade) {
            if ($grade->level != $newLevel) {
                $grade->level = $newLevel;
                $grade->save();
            }
            $newLevel++;
        }
    }
}