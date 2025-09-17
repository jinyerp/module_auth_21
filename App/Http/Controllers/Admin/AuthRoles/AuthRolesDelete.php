<?php

namespace Jiny\Auth\App\Http\Controllers\Admin\AuthRoles;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuthRolesDelete extends Controller
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
            $jsonData['template']['delete'] = 'jiny-admin::crud.delete';
            
            return $jsonData;
        }
        return [];
    }

    public function index(Request $request, $id)
    {
        return view($this->jsonData['template']['delete'], [
            'jsonData' => $this->jsonData,
            'id' => $id
        ]);
    }

    /**
     * Hook: 삭제 가능 여부 확인
     */
    public function hookCanDelete($wire, $model)
    {
        if (!$model) {
            return false;
        }
        
        // 시스템 역할 보호
        if ($this->isSystemRole($model)) {
            $wire->addError('delete', '시스템 역할은 삭제할 수 없습니다.');
            return false;
        }
        
        // 사용중인 역할 확인
        $userCount = DB::table('role_account')
            ->where('role_id', $model->id)
            ->count();
        
        if ($userCount > 0) {
            $wire->addError('delete', "이 역할을 사용중인 사용자가 {$userCount}명 있습니다. 먼저 사용자의 역할을 변경해주세요.");
            return false;
        }
        
        return true;
    }

    /**
     * Hook: 삭제 전 처리
     */
    public function hookDeleting($wire, $id)
    {
        // 삭제할 역할 정보 조회
        $model = DB::table('accounts_roles')->find($id);
        
        if (!$model) {
            return "역할을 찾을 수 없습니다.";
        }
        
        // 시스템 역할 보호
        if ($this->isSystemRole($model)) {
            return "시스템 역할은 삭제할 수 없습니다.";
        }
        
        // 관련 데이터 확인
        $hasRelations = $this->checkRelations($id);
        if ($hasRelations) {
            return "이 역할과 연결된 데이터가 있어 삭제할 수 없습니다.";
        }
        
        // 삭제 로그 기록 (삭제 전)
        activity()
            ->performedOn($model)
            ->log("역할 삭제 준비: {$model->name}");
        
        return true; // 삭제 진행
    }

    /**
     * Hook: 소프트 삭제 처리
     */
    public function hookSoftDeleting($wire, $model)
    {
        // 소프트 삭제 처리
        $data = [
            'deleted_at' => now(),
            'deleted_by' => auth()->id() ?? null
        ];
        
        DB::table('accounts_roles')
            ->where('id', $model->id)
            ->update($data);
        
        // 로그 기록
        activity()
            ->performedOn($model)
            ->log("역할 소프트 삭제: {$model->name}");
        
        return true;
    }

    /**
     * Hook: 삭제 후 처리
     */
    public function hookDeleted($wire, $id)
    {
        // 관련 권한 매핑 제거
        DB::table('role_account')->where('role_id', $id)->delete();
        
        // 캐시 클리어
        // Cache::forget('roles');
        // Cache::forget("role_{$id}");
        
        // 성공 메시지
        session()->flash('message', '역할이 성공적으로 삭제되었습니다.');
        
        // 로그 기록
        activity()->log("역할 삭제 완료: ID {$id}");
    }

    /**
     * Hook: 복원 처리
     */
    public function hookRestoring($wire, $model)
    {
        // 소프트 삭제된 항목 복원
        DB::table('accounts_roles')
            ->where('id', $model->id)
            ->update([
                'deleted_at' => null,
                'deleted_by' => null
            ]);
        
        // 로그 기록
        activity()
            ->performedOn($model)
            ->log("역할 복원: {$model->name}");
        
        // 성공 메시지
        session()->flash('message', "역할 '{$model->name}'이(가) 복원되었습니다.");
        
        return true;
    }

    /**
     * 시스템 역할 확인
     */
    protected function isSystemRole($model)
    {
        $systemRoles = ['super-admin', 'admin', 'user'];
        return in_array($model->slug, $systemRoles);
    }

    /**
     * 관련 데이터 확인
     */
    protected function checkRelations($roleId)
    {
        // role_account 테이블 확인
        $count = DB::table('role_account')
            ->where('role_id', $roleId)
            ->count();
        
        return $count > 0;
    }
}