<?php
namespace Jiny\Auth\App\Http\Controllers\Admin\AuthGrades;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Jiny\Auth\App\Models\Grade;

/**
 * 회원 등급 관리 메인 컨트롤러
 * @jiny/admin 패턴을 따르는 CRUD 컨트롤러
 */
class AuthGrades extends Controller
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
        
        return [
            'title' => '회원 등급 관리',
            'description' => '회원 등급 및 혜택 관리',
            'model' => 'Jiny\\Auth\\App\\Models\\Grade',
            'table' => 'accounts_grades',
            'primaryKey' => 'id',
            'perPage' => 20,
            'searchable' => ['name', 'code', 'description'],
            'sortable' => ['level', 'name', 'code', 'created_at']
        ];
    }

    /**
     * 회원 등급 목록 페이지
     */
    public function index(Request $request)
    {
        // Hook 시스템을 위한 controllerClass 설정
        $this->jsonData['controllerClass'] = static::class;
        
        // 템플릿 경로 설정
        $this->jsonData['template'] = [
            'layout' => 'jiny-admin::crud.index',
            'table' => $this->baseViewPath . '.table',
            'search' => $this->baseViewPath . '.search'
        ];

        // 라우트 정보 설정
        $this->jsonData['routes'] = [
            'create' => route('admin.auth.grades.create'),
            'show' => 'admin.auth.grades.show',
            'edit' => 'admin.auth.grades.edit',
            'delete' => 'admin.auth.grades.delete'
        ];

        return view('jiny-admin::crud.index', [
            'jsonData' => $this->jsonData,
            'controllerClass' => static::class
        ]);
    }

    /**
     * Hook: 목록 조회 전 처리
     * 레벨 기준 기본 정렬 적용
     */
    public function hookIndexing($livewire)
    {
        // 기본 정렬을 레벨 기준으로 설정
        if (!$livewire->sortField) {
            $livewire->sortField = 'level';
            $livewire->sortDirection = 'asc';
        }
    }

    /**
     * Hook: 목록 데이터 가공
     * 등급별 사용자 수 추가
     */
    public function hookIndexed($livewire, $rows)
    {
        // 각 등급별 사용자 수 계산
        foreach ($rows as $row) {
            $row->user_count = DB::table('accounts')
                ->where('grade_id', $row->id)
                ->count();
            
            // 혜택을 읽기 쉬운 형태로 변환
            if ($row->benefits) {
                $benefits = is_string($row->benefits) ? json_decode($row->benefits, true) : $row->benefits;
                $row->benefits_display = $this->formatBenefits($benefits);
            }
        }
        
        return $rows;
    }

    /**
     * Hook: 테이블 헤더 커스터마이징
     */
    public function hookTableHeader($livewire)
    {
        return [
            'level' => ['label' => '레벨', 'sortable' => true, 'width' => '80px'],
            'name' => ['label' => '등급명', 'sortable' => true],
            'code' => ['label' => '등급 코드', 'sortable' => true],
            'point_rate' => ['label' => '포인트 적립률', 'sortable' => true],
            'discount_rate' => ['label' => '할인율(%)', 'sortable' => true],
            'user_count' => ['label' => '회원 수', 'sortable' => false],
            'is_active' => ['label' => '상태', 'sortable' => true],
            'created_at' => ['label' => '생성일', 'sortable' => true]
        ];
    }

    /**
     * Hook: 검색 필드 설정
     */
    public function hookSearch($livewire)
    {
        return [
            'fields' => ['name', 'code', 'description'],
            'placeholder' => '등급명, 코드, 설명 검색...'
        ];
    }

    /**
     * Hook: 필터 설정
     */
    public function hookFilters($livewire)
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
            ],
            'level' => [
                'label' => '레벨',
                'type' => 'range',
                'min' => 1,
                'max' => 10
            ]
        ];
    }

    /**
     * Hook: 등급 계산 로직
     * 사용자의 구매 이력을 기반으로 적절한 등급 계산
     */
    public function hookGradeCalculation($userId, $purchaseHistory = null)
    {
        // 구매 이력 기반 등급 계산 로직
        $totalPurchase = $purchaseHistory['total'] ?? 0;
        $purchaseCount = $purchaseHistory['count'] ?? 0;
        
        // 등급별 승급 조건 확인
        $grades = Grade::where('is_active', true)
            ->orderBy('level', 'desc')
            ->get();
        
        foreach ($grades as $grade) {
            if ($totalPurchase >= $grade->min_purchase) {
                return $grade->id;
            }
        }
        
        // 기본 등급 반환
        return Grade::where('level', 1)->first()->id ?? null;
    }

    /**
     * 혜택 정보 포맷팅
     */
    private function formatBenefits($benefits)
    {
        if (!$benefits || !is_array($benefits)) {
            return '';
        }
        
        $formatted = [];
        foreach ($benefits as $key => $value) {
            $formatted[] = "{$key}: {$value}";
        }
        
        return implode(', ', $formatted);
    }
}