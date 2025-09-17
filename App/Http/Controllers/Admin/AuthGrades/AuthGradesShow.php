<?php
namespace Jiny\Auth\App\Http\Controllers\Admin\AuthGrades;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\App\Models\Grade;
use Illuminate\Support\Facades\DB;

/**
 * 회원 등급 상세보기 컨트롤러
 */
class AuthGradesShow extends Controller
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
     * 회원 등급 상세 페이지
     */
    public function index(Request $request, $id)
    {
        $grade = Grade::findOrFail($id);
        
        // Hook 시스템을 위한 controllerClass 설정
        $this->jsonData['controllerClass'] = static::class;
        
        // 템플릿 경로 설정
        $this->jsonData['template'] = [
            'layout' => 'jiny-admin::crud.show',
            'detail' => $this->baseViewPath . '.show'
        ];

        // 라우트 정보 설정
        $this->jsonData['routes'] = [
            'edit' => route('admin.auth.grades.edit', $id),
            'delete' => route('admin.auth.grades.delete', $id),
            'list' => route('admin.auth.grades')
        ];

        // 추가 데이터 로드
        $grade = $this->loadAdditionalData($grade);

        return view('jiny-admin::crud.show', [
            'jsonData' => $this->jsonData,
            'data' => $grade,
            'id' => $id,
            'controllerClass' => static::class
        ]);
    }

    /**
     * Hook: 표시 전 처리
     */
    public function hookShowing($livewire, $id)
    {
        // 접근 권한 확인
        if (!auth()->user()->can('view', Grade::class)) {
            abort(403, '이 등급을 볼 권한이 없습니다.');
        }
        
        // 뷰 카운트 증가 (선택적)
        DB::table('accounts_grades')
            ->where('id', $id)
            ->increment('view_count');
    }

    /**
     * Hook: 데이터 로드 후 처리
     */
    public function hookShowed($livewire, $record)
    {
        // 혜택 정보 디코딩
        if ($record && isset($record->benefits)) {
            if (is_string($record->benefits)) {
                $record->benefits = json_decode($record->benefits, true);
            }
        }
        
        // 통계 정보 추가
        $record->statistics = $this->getGradeStatistics($record->id);
        
        // 관련 사용자 샘플
        $record->sample_users = $this->getSampleUsers($record->id);
        
        // 승급/강등 규칙
        $record->promotion_rules = $this->getPromotionRules($record);
        
        return $record;
    }

    /**
     * Hook: 상세 필드 커스터마이징
     */
    public function hookDetailFields($livewire)
    {
        return [
            'basic_info' => [
                'title' => '기본 정보',
                'fields' => [
                    'name' => ['label' => '등급명', 'icon' => 'badge'],
                    'code' => ['label' => '등급 코드', 'icon' => 'key'],
                    'level' => ['label' => '레벨', 'icon' => 'chart-bar'],
                    'is_active' => ['label' => '상태', 'type' => 'boolean', 'icon' => 'check-circle']
                ]
            ],
            'benefits' => [
                'title' => '혜택 정보',
                'fields' => [
                    'point_rate' => ['label' => '포인트 적립률', 'format' => 'percentage', 'icon' => 'star'],
                    'discount_rate' => ['label' => '할인율(%)', 'icon' => 'tag'],
                    'min_purchase' => ['label' => '최소 구매금액', 'format' => 'currency', 'icon' => 'currency-dollar'],
                    'benefits' => ['label' => '추가 혜택', 'type' => 'json', 'icon' => 'gift']
                ]
            ],
            'visual' => [
                'title' => '시각적 설정',
                'fields' => [
                    'color' => ['label' => '등급 색상', 'type' => 'color', 'icon' => 'color-swatch'],
                    'icon' => ['label' => '아이콘', 'icon' => 'photograph']
                ]
            ],
            'meta' => [
                'title' => '메타 정보',
                'fields' => [
                    'description' => ['label' => '설명', 'type' => 'text', 'icon' => 'document-text'],
                    'created_at' => ['label' => '생성일', 'format' => 'datetime', 'icon' => 'calendar'],
                    'updated_at' => ['label' => '수정일', 'format' => 'datetime', 'icon' => 'clock']
                ]
            ]
        ];
    }

    /**
     * Hook: 관련 데이터 로드
     */
    public function hookRelatedData($livewire, $model)
    {
        return [
            'users' => [
                'title' => '이 등급의 회원',
                'count' => DB::table('accounts')->where('grade_id', $model->id)->count(),
                'recent' => DB::table('accounts')
                    ->where('grade_id', $model->id)
                    ->latest()
                    ->limit(5)
                    ->get()
            ],
            'upgrades' => [
                'title' => '최근 승급 이력',
                'count' => DB::table('grade_histories')
                    ->where('new_grade_id', $model->id)
                    ->count(),
                'recent' => DB::table('grade_histories')
                    ->where('new_grade_id', $model->id)
                    ->latest()
                    ->limit(5)
                    ->get()
            ]
        ];
    }

    /**
     * 추가 데이터 로드
     */
    private function loadAdditionalData($grade)
    {
        // 사용자 수
        $grade->user_count = DB::table('accounts')
            ->where('grade_id', $grade->id)
            ->count();
        
        // 활성 사용자 수
        $grade->active_user_count = DB::table('accounts')
            ->where('grade_id', $grade->id)
            ->where('is_active', true)
            ->count();
        
        // 혜택 정보 포맷팅
        if ($grade->benefits) {
            $benefits = is_string($grade->benefits) ? json_decode($grade->benefits, true) : $grade->benefits;
            $grade->benefits_formatted = $this->formatBenefits($benefits);
        }
        
        // 다음 등급 정보
        $grade->next_grade = Grade::where('level', '>', $grade->level)
            ->orderBy('level')
            ->first();
        
        // 이전 등급 정보
        $grade->prev_grade = Grade::where('level', '<', $grade->level)
            ->orderBy('level', 'desc')
            ->first();
        
        return $grade;
    }

    /**
     * 등급 통계 정보 조회
     */
    private function getGradeStatistics($gradeId)
    {
        return [
            'total_users' => DB::table('accounts')->where('grade_id', $gradeId)->count(),
            'active_users' => DB::table('accounts')->where('grade_id', $gradeId)->where('is_active', true)->count(),
            'new_this_month' => DB::table('accounts')
                ->where('grade_id', $gradeId)
                ->where('created_at', '>=', now()->startOfMonth())
                ->count(),
            'avg_purchase' => DB::table('orders')
                ->join('accounts', 'orders.user_id', '=', 'accounts.id')
                ->where('accounts.grade_id', $gradeId)
                ->avg('orders.total_amount') ?? 0
        ];
    }

    /**
     * 샘플 사용자 조회
     */
    private function getSampleUsers($gradeId, $limit = 5)
    {
        return DB::table('accounts')
            ->where('grade_id', $gradeId)
            ->select('id', 'name', 'email', 'created_at')
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * 승급 규칙 정보
     */
    private function getPromotionRules($grade)
    {
        $nextGrade = Grade::where('level', '>', $grade->level)
            ->orderBy('level')
            ->first();
        
        if (!$nextGrade) {
            return ['message' => '최고 등급입니다.'];
        }
        
        return [
            'next_grade' => $nextGrade->name,
            'requirements' => [
                'min_purchase' => $nextGrade->min_purchase,
                'description' => "누적 구매금액이 " . number_format($nextGrade->min_purchase) . "원 이상이면 {$nextGrade->name} 등급으로 승급됩니다."
            ]
        ];
    }

    /**
     * 혜택 정보 포맷팅
     */
    private function formatBenefits($benefits)
    {
        if (!$benefits || !is_array($benefits)) {
            return [];
        }
        
        $formatted = [];
        foreach ($benefits as $key => $value) {
            $formatted[] = [
                'name' => $this->translateBenefitKey($key),
                'value' => $value,
                'icon' => $this->getBenefitIcon($key)
            ];
        }
        
        return $formatted;
    }

    /**
     * 혜택 키 번역
     */
    private function translateBenefitKey($key)
    {
        $translations = [
            'free_shipping' => '무료 배송',
            'birthday_coupon' => '생일 쿠폰',
            'exclusive_sale' => '전용 세일',
            'priority_support' => '우선 고객지원',
            'early_access' => '신제품 우선 구매'
        ];
        
        return $translations[$key] ?? $key;
    }

    /**
     * 혜택 아이콘 매핑
     */
    private function getBenefitIcon($key)
    {
        $icons = [
            'free_shipping' => 'truck',
            'birthday_coupon' => 'cake',
            'exclusive_sale' => 'tag',
            'priority_support' => 'support',
            'early_access' => 'clock'
        ];
        
        return $icons[$key] ?? 'star';
    }
}