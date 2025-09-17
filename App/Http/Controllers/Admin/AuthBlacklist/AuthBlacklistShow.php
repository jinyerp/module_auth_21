<?php

namespace Jiny\Auth\App\Http\Controllers\Admin\AuthBlacklist;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\App\Models\Blacklist;
use Jiny\Auth\App\Models\Account;

class AuthBlacklistShow extends Controller
{
    private $jsonData;

    public function __construct()
    {
        $this->jsonData = $this->loadJsonData();
    }

    protected function loadJsonData()
    {
        $jsonPath = dirname(__FILE__) . '/AuthBlacklist.json';
        
        if (file_exists($jsonPath)) {
            $jsonContent = file_get_contents($jsonPath);
            return json_decode($jsonContent, true);
        }

        return [];
    }

    public function index($id)
    {
        $data = Blacklist::findOrFail($id);

        $this->jsonData['route'] = [
            'name' => 'admin.auth.blacklist',
            'show' => 'admin.auth.blacklist.show',
            'edit' => 'admin.auth.blacklist.edit',
            'delete' => 'admin.auth.blacklist.delete'
        ];

        $this->jsonData['template'] = [
            'show' => 'jiny-auth::admin.auth_blacklist.show'
        ];

        // controllerClass 설정 (Hook 시스템 활성화)
        $this->jsonData['controllerClass'] = self::class;

        return view('jiny-admin::crud.show', [
            'jsonData' => $this->jsonData,
            'data' => $data,
            'id' => $id
        ]);
    }

    /**
     * Hook: 상세 보기 데이터 로드 전
     */
    public function hookShowing($wire, $id)
    {
        // 조회 기록 남기기
        activity()
            ->withProperties(['blacklist_id' => $id])
            ->log('Blacklist entry viewed');
    }

    /**
     * Hook: 상세 보기 데이터 가공
     */
    public function hookShowed($wire, $record)
    {
        // 관계 데이터 로드
        if ($record->added_by) {
            $addedBy = Account::find($record->added_by);
            $record->added_by_name = $addedBy ? $addedBy->name : 'Unknown';
            $record->added_by_email = $addedBy ? $addedBy->email : '';
        } else {
            $record->added_by_name = 'System';
            $record->added_by_email = '';
        }

        // 상태 정보 추가
        $record->status_info = $this->getStatusInfo($record);

        // 타입 라벨
        $typeLabels = [
            'email' => '이메일',
            'ip' => 'IP 주소',
            'phone' => '전화번호',
            'domain' => '도메인',
            'user_agent' => 'User Agent',
            'account' => '계정'
        ];
        $record->type_label = $typeLabels[$record->type] ?? $record->type;

        // 영구 차단 여부
        $record->is_permanent = !$record->expires_at;

        // 만료 상태
        if ($record->expires_at) {
            if ($record->expires_at <= now()) {
                $record->expiry_status = '만료됨';
                $record->expiry_status_color = 'red';
            } else {
                $record->expiry_status = '유효';
                $record->expiry_status_color = 'green';
                $record->expires_in = $record->expires_at->diffForHumans();
            }
        } else {
            $record->expiry_status = '영구';
            $record->expiry_status_color = 'blue';
        }

        // 메타데이터 파싱
        if ($record->meta) {
            $record->meta_parsed = is_string($record->meta) ? json_decode($record->meta, true) : $record->meta;
            
            // 변경 이력 추출
            if (isset($record->meta_parsed['modification_history'])) {
                $record->modification_history = $record->meta_parsed['modification_history'];
            }
        }

        // 통계 정보
        $record->statistics = $this->getStatistics($record);

        // 관련 차단 항목 찾기
        $record->related_entries = $this->findRelatedEntries($record);

        return $record;
    }

    /**
     * Hook: 상세 필드 구성
     */
    public function hookDetailFields($wire)
    {
        return [
            'basic_info' => [
                'title' => '기본 정보',
                'fields' => [
                    'id' => ['label' => 'ID', 'type' => 'text'],
                    'type_label' => ['label' => '차단 유형', 'type' => 'badge'],
                    'value' => ['label' => '차단 대상', 'type' => 'code'],
                    'reason' => ['label' => '차단 사유', 'type' => 'text'],
                    'description' => ['label' => '상세 설명', 'type' => 'textarea']
                ]
            ],
            'status_info' => [
                'title' => '상태 정보',
                'fields' => [
                    'is_active' => ['label' => '활성화 상태', 'type' => 'boolean'],
                    'expiry_status' => ['label' => '만료 상태', 'type' => 'badge'],
                    'expires_at' => ['label' => '만료 일시', 'type' => 'datetime'],
                    'expires_in' => ['label' => '남은 기간', 'type' => 'text']
                ]
            ],
            'statistics' => [
                'title' => '차단 통계',
                'fields' => [
                    'hit_count' => ['label' => '차단 횟수', 'type' => 'number'],
                    'last_hit_at' => ['label' => '마지막 차단', 'type' => 'datetime'],
                    'daily_average' => ['label' => '일평균 차단', 'type' => 'number']
                ]
            ],
            'audit_info' => [
                'title' => '감사 정보',
                'fields' => [
                    'added_by_name' => ['label' => '추가자', 'type' => 'text'],
                    'created_at' => ['label' => '추가 일시', 'type' => 'datetime'],
                    'updated_at' => ['label' => '수정 일시', 'type' => 'datetime']
                ]
            ]
        ];
    }

    /**
     * Hook: 관련 데이터 로드
     */
    public function hookRelatedData($wire, $model)
    {
        // 동일 IP의 다른 차단
        if ($model->type === 'ip') {
            $relatedBlacklists = Blacklist::where('type', 'ip')
                ->where('value', 'LIKE', substr($model->value, 0, strrpos($model->value, '.')) . '.%')
                ->where('id', '!=', $model->id)
                ->limit(5)
                ->get();
            
            $model->related_ip_blocks = $relatedBlacklists;
        }

        // 동일 도메인의 이메일 차단
        if ($model->type === 'email') {
            $domain = substr(strrchr($model->value, "@"), 1);
            if ($domain) {
                $relatedEmails = Blacklist::where('type', 'email')
                    ->where('value', 'LIKE', '%@' . $domain)
                    ->where('id', '!=', $model->id)
                    ->limit(5)
                    ->get();
                
                $model->related_email_blocks = $relatedEmails;
            }
        }

        return $model;
    }

    /**
     * 상태 정보 가져오기
     */
    protected function getStatusInfo($record)
    {
        $info = [];

        if ($record->is_active) {
            $info['status'] = '활성';
            $info['color'] = 'green';
            
            if ($record->expires_at && $record->expires_at <= now()) {
                $info['status'] = '만료됨';
                $info['color'] = 'yellow';
                $info['message'] = '만료되었지만 아직 비활성화되지 않음';
            }
        } else {
            $info['status'] = '비활성';
            $info['color'] = 'gray';
        }

        return $info;
    }

    /**
     * 통계 정보 계산
     */
    protected function getStatistics($record)
    {
        $stats = [
            'total_hits' => $record->hit_count,
            'last_hit' => $record->last_hit_at ? $record->last_hit_at->format('Y-m-d H:i:s') : 'N/A',
            'days_active' => $record->created_at->diffInDays(now()),
            'daily_average' => 0
        ];

        if ($stats['days_active'] > 0) {
            $stats['daily_average'] = round($record->hit_count / $stats['days_active'], 2);
        }

        // 효과성 평가
        if ($record->hit_count > 100) {
            $stats['effectiveness'] = '매우 효과적';
            $stats['effectiveness_color'] = 'green';
        } elseif ($record->hit_count > 10) {
            $stats['effectiveness'] = '효과적';
            $stats['effectiveness_color'] = 'blue';
        } elseif ($record->hit_count > 0) {
            $stats['effectiveness'] = '보통';
            $stats['effectiveness_color'] = 'yellow';
        } else {
            $stats['effectiveness'] = '미사용';
            $stats['effectiveness_color'] = 'gray';
        }

        return $stats;
    }

    /**
     * 관련 차단 항목 찾기
     */
    protected function findRelatedEntries($record)
    {
        $related = [];

        // 같은 이유로 차단된 항목
        $sameReason = Blacklist::where('reason', $record->reason)
            ->where('id', '!=', $record->id)
            ->limit(3)
            ->get(['id', 'type', 'value']);
        
        if ($sameReason->isNotEmpty()) {
            $related['same_reason'] = $sameReason;
        }

        // 같은 추가자가 추가한 항목
        if ($record->added_by) {
            $sameAdder = Blacklist::where('added_by', $record->added_by)
                ->where('id', '!=', $record->id)
                ->orderBy('created_at', 'desc')
                ->limit(3)
                ->get(['id', 'type', 'value', 'created_at']);
            
            if ($sameAdder->isNotEmpty()) {
                $related['same_adder'] = $sameAdder;
            }
        }

        return $related;
    }

    /**
     * Hook: 커스텀 액션 - 테스트 차단
     */
    public function hookCustomTestBlock($wire, $params)
    {
        $id = $params['id'] ?? null;
        if (!$id) {
            return "Invalid ID";
        }

        $blacklist = Blacklist::find($id);
        if (!$blacklist) {
            return "Blacklist entry not found";
        }

        // 테스트 차단 실행
        $result = false;
        switch ($blacklist->type) {
            case 'email':
                $result = Blacklist::isEmailBlacklisted($blacklist->value);
                break;
            case 'ip':
                $result = Blacklist::isIpBlacklisted($blacklist->value);
                break;
            case 'phone':
                $result = Blacklist::isPhoneBlacklisted($blacklist->value);
                break;
            default:
                $result = Blacklist::isBlacklisted($blacklist->type, $blacklist->value);
        }

        if ($result) {
            session()->flash('message', '차단이 정상적으로 작동합니다.');
        } else {
            session()->flash('error', '차단이 작동하지 않습니다. 상태를 확인하세요.');
        }

        return ['success' => $result];
    }
}