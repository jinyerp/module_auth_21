<?php

namespace Jiny\Auth\App\Http\Controllers\Admin\AuthRoles;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuthRolesShow extends Controller
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
            $jsonData['template']['show'] = 'jiny-admin::crud.show';
            $jsonData['template']['detail'] = 'jiny-auth::admin.auth_roles.show';
            
            return $jsonData;
        }
        return [];
    }

    public function index(Request $request, $id)
    {
        // 데이터 조회
        $data = DB::table('accounts_roles')->find($id);
        
        if (!$data) {
            abort(404, '역할을 찾을 수 없습니다.');
        }
        
        return view($this->jsonData['template']['show'], [
            'jsonData' => $this->jsonData,
            'data' => $data,
            'id' => $id,
            'controllerClass' => self::class
        ]);
    }

    /**
     * Hook: 표시 전 데이터 로드
     */
    public function hookShowing($wire, $id)
    {
        // 추가 데이터 로드가 필요한 경우
        // 예: 권한 확인
        if (!auth()->user() || !auth()->user()->can('view-roles')) {
            // $wire->addError('permission', '역할을 볼 권한이 없습니다.');
            // return false;
        }
    }

    /**
     * Hook: 표시 데이터 가공
     */
    public function hookShowed($wire, $record)
    {
        if (!$record) {
            return $record;
        }
        
        // permissions JSON 디코드
        if (isset($record->permissions) && is_string($record->permissions)) {
            $record->permissions_decoded = json_decode($record->permissions, true);
            $record->permissions_pretty = json_encode(
                json_decode($record->permissions, true), 
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
            );
        }
        
        // 사용자 수 계산
        $record->user_count = DB::table('role_account')
            ->where('role_id', $record->id)
            ->count();
        
        // 관련 사용자 목록 (최대 10명)
        $record->recent_users = DB::table('role_account')
            ->join('accounts', 'role_account.account_id', '=', 'accounts.id')
            ->where('role_account.role_id', $record->id)
            ->select('accounts.id', 'accounts.name', 'accounts.email')
            ->limit(10)
            ->get();
        
        // 시스템 역할 여부
        $systemRoles = ['super-admin', 'admin', 'user'];
        $record->is_system = in_array($record->slug, $systemRoles);
        
        // 상태 레이블
        $record->status_label = $record->is_active ? '활성' : '비활성';
        $record->status_class = $record->is_active ? 'success' : 'danger';
        
        return $record;
    }

    /**
     * Hook: 상세 필드 커스터마이징
     */
    public function hookDetailFields($wire)
    {
        return [
            'basic_info' => [
                'title' => '기본 정보',
                'fields' => [
                    'id' => ['label' => 'ID', 'type' => 'text'],
                    'name' => ['label' => '역할명', 'type' => 'text'],
                    'slug' => ['label' => '슬러그', 'type' => 'text'],
                    'description' => ['label' => '설명', 'type' => 'textarea'],
                    'is_active' => ['label' => '상태', 'type' => 'badge']
                ]
            ],
            'permissions' => [
                'title' => '권한 설정',
                'fields' => [
                    'permissions_pretty' => ['label' => '권한 목록', 'type' => 'json']
                ]
            ],
            'statistics' => [
                'title' => '통계',
                'fields' => [
                    'user_count' => ['label' => '사용자 수', 'type' => 'number']
                ]
            ],
            'timestamps' => [
                'title' => '시스템 정보',
                'fields' => [
                    'created_at' => ['label' => '생성일', 'type' => 'datetime'],
                    'updated_at' => ['label' => '수정일', 'type' => 'datetime']
                ]
            ]
        ];
    }

    /**
     * Hook: 관련 데이터 로드
     */
    public function hookRelatedData($wire, $model)
    {
        // 관련 사용자 목록
        $relatedUsers = DB::table('role_account')
            ->join('accounts', 'role_account.account_id', '=', 'accounts.id')
            ->where('role_account.role_id', $model->id)
            ->select(
                'accounts.id',
                'accounts.name',
                'accounts.email',
                'role_account.created_at as assigned_at'
            )
            ->paginate(20);
        
        return [
            'users' => $relatedUsers
        ];
    }

    /**
     * Hook: 커스텀 액션 - 활성화 토글
     */
    public function hookCustomToggleActive($wire, $params)
    {
        $id = $params['id'] ?? null;
        if (!$id) {
            return "ID가 제공되지 않았습니다.";
        }
        
        $role = DB::table('accounts_roles')->find($id);
        if (!$role) {
            return "역할을 찾을 수 없습니다.";
        }
        
        // 상태 토글
        $newStatus = !$role->is_active;
        DB::table('accounts_roles')
            ->where('id', $id)
            ->update([
                'is_active' => $newStatus,
                'updated_at' => now()
            ]);
        
        // 로그 기록
        $statusText = $newStatus ? '활성화' : '비활성화';
        activity()->log("역할 {$statusText}: {$role->name}");
        
        // 성공 메시지
        session()->flash('message', "역할이 {$statusText}되었습니다.");
        
        return ['success' => true];
    }

    /**
     * Hook: 커스텀 액션 - 권한 복사
     */
    public function hookCustomCloneRole($wire, $params)
    {
        $id = $params['id'] ?? null;
        if (!$id) {
            return "ID가 제공되지 않았습니다.";
        }
        
        $role = DB::table('accounts_roles')->find($id);
        if (!$role) {
            return "역할을 찾을 수 없습니다.";
        }
        
        // 새 역할 생성
        $newSlug = $role->slug . '-copy-' . time();
        $newName = $role->name . ' (복사본)';
        
        $newId = DB::table('accounts_roles')->insertGetId([
            'name' => $newName,
            'slug' => $newSlug,
            'description' => $role->description . ' (복사됨)',
            'permissions' => $role->permissions,
            'is_active' => false, // 복사본은 비활성 상태로 시작
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        // 로그 기록
        activity()->log("역할 복사: {$role->name} → {$newName}");
        
        // 성공 메시지
        session()->flash('message', "역할이 복사되었습니다. 새 역할: {$newName}");
        
        return ['success' => true, 'new_id' => $newId];
    }
}