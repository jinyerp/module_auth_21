<?php

namespace Jiny\Auth\App\Http\Controllers\Admin\AuthRoles;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class AuthRoles extends Controller
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
            
            // controllerClass 추가 (Hook 시스템 활성화를 위해 필수)
            $jsonData['controllerClass'] = self::class;
            
            // 기본 설정 추가
            if (!isset($jsonData['route'])) {
                $jsonData['route'] = [
                    'name' => 'admin.auth.roles',
                    'prefix' => 'admin/auth/roles'
                ];
            }
            
            if (!isset($jsonData['template'])) {
                $jsonData['template'] = [
                    'index' => 'jiny-admin::crud.index',
                    'table' => 'jiny-auth::admin.auth_roles.table',
                    'search' => 'jiny-auth::admin.auth_roles.search'
                ];
            }
            
            return $jsonData;
        }
        return [];
    }

    public function index(Request $request)
    {
        return view($this->jsonData['template']['index'], [
            'jsonData' => $this->jsonData
        ]);
    }

    /**
     * Hook: 목록 조회 전 처리
     */
    public function hookIndexing($wire)
    {
        // 쿼리 커스터마이징이 필요한 경우
        // 예: 특정 권한만 표시
        // $wire->query->where('is_system', false);
    }

    /**
     * Hook: 목록 데이터 가공
     */
    public function hookIndexed($wire, $rows)
    {
        // 각 역할에 대한 추가 정보 가공
        foreach ($rows as $row) {
            // 권한 개수 계산
            if ($row->permissions) {
                $permissions = is_string($row->permissions) ? json_decode($row->permissions, true) : $row->permissions;
                $row->permission_count = is_array($permissions) ? count($permissions) : 0;
            } else {
                $row->permission_count = 0;
            }
            
            // 사용자 수 계산 (관계가 설정된 경우)
            // $row->user_count = DB::table('role_account')->where('role_id', $row->id)->count();
        }
        
        return $rows;
    }

    /**
     * Hook: 테이블 헤더 커스터마이징
     */
    public function hookTableHeader($wire)
    {
        return [
            'id' => ['label' => 'ID', 'width' => '60px'],
            'name' => ['label' => '역할명', 'sortable' => true],
            'slug' => ['label' => '슬러그', 'sortable' => true],
            'description' => ['label' => '설명'],
            'permission_count' => ['label' => '권한 수'],
            'is_active' => ['label' => '상태', 'align' => 'center', 'width' => '80px'],
            'created_at' => ['label' => '생성일', 'sortable' => true, 'width' => '150px']
        ];
    }

    /**
     * Hook: 검색 설정
     */
    public function hookSearch($wire)
    {
        // 검색 가능한 필드 정의
        return [
            'searchable' => ['name', 'slug', 'description'],
            'placeholder' => '역할명, 슬러그, 설명 검색...'
        ];
    }

    /**
     * Hook: 필터 설정
     */
    public function hookFilters($wire)
    {
        return [
            'is_active' => [
                'label' => '상태',
                'type' => 'select',
                'options' => [
                    '' => '전체',
                    '1' => '활성',
                    '0' => '비활성'
                ]
            ]
        ];
    }

    /**
     * Hook: 정렬 설정
     */
    public function hookSorting($wire)
    {
        return [
            'default' => 'name',
            'direction' => 'asc',
            'sortable' => ['name', 'slug', 'created_at']
        ];
    }

    /**
     * Hook: 페이지네이션 설정
     */
    public function hookPagination($wire)
    {
        return [
            'perPage' => 20,
            'perPageOptions' => [10, 20, 50, 100]
        ];
    }

    /**
     * Hook: 테이블 행 액션
     */
    public function hookActions($wire)
    {
        return [
            'view' => ['enabled' => true, 'icon' => 'eye'],
            'edit' => ['enabled' => true, 'icon' => 'pencil'],
            'delete' => ['enabled' => true, 'icon' => 'trash']
        ];
    }

    /**
     * Hook: 대량 작업
     */
    public function hookBulkActions($wire)
    {
        return [
            'delete' => ['label' => '선택 삭제', 'confirm' => true],
            'activate' => ['label' => '활성화'],
            'deactivate' => ['label' => '비활성화']
        ];
    }
}