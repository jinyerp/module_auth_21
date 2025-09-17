<?php

namespace Jiny\Auth\App\Http\Controllers\Admin\AuthBlacklist;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Jiny\Auth\App\Models\Blacklist;
use Jiny\Auth\App\Models\Account;

class AuthBlacklist extends Controller
{
    private $jsonData;

    public function __construct()
    {
        $this->jsonData = $this->loadJsonData();
    }

    protected function loadJsonData()
    {
        $jsonPath = __DIR__ . '/AuthBlacklist.json';
        
        if (file_exists($jsonPath)) {
            $jsonContent = file_get_contents($jsonPath);
            return json_decode($jsonContent, true);
        }

        return [];
    }

    public function index()
    {
        $this->jsonData['route'] = [
            'name' => 'admin.auth.blacklist',
            'create' => 'admin.auth.blacklist.create',
            'edit' => 'admin.auth.blacklist.edit',
            'show' => 'admin.auth.blacklist.show',
            'delete' => 'admin.auth.blacklist.delete'
        ];

        $this->jsonData['template'] = [
            'index' => 'jiny-auth::admin.auth_blacklist.table',
            'create' => 'jiny-auth::admin.auth_blacklist.create',
            'edit' => 'jiny-auth::admin.auth_blacklist.edit', 
            'show' => 'jiny-auth::admin.auth_blacklist.show',
            'delete' => 'jiny-auth::admin.auth_blacklist.delete',
            'search' => 'jiny-auth::admin.auth_blacklist.search'
        ];

        // controllerClass 설정 (Hook 시스템 활성화)
        $this->jsonData['controllerClass'] = self::class;

        return view('jiny-admin::crud.index', [
            'jsonData' => $this->jsonData
        ]);
    }

    /**
     * Hook: 목록 조회 전 처리
     */
    public function hookIndexing($wire)
    {
        // 만료된 블랙리스트 자동 비활성화
        Blacklist::deactivateExpired();
    }

    /**
     * Hook: 목록 데이터 가공
     */
    public function hookIndexed($wire, $rows)
    {
        foreach ($rows as $row) {
            // 차단자 이름 추가
            if ($row->added_by) {
                $addedBy = Account::find($row->added_by);
                $row->blocked_by_name = $addedBy ? $addedBy->name : 'Unknown';
            } else {
                $row->blocked_by_name = 'System';
            }

            // 상태 라벨 추가
            if ($row->is_active) {
                if ($row->expires_at && $row->expires_at <= now()) {
                    $row->status_label = '만료됨';
                    $row->status_color = 'yellow';
                } else {
                    $row->status_label = '활성';
                    $row->status_color = 'green';
                }
            } else {
                $row->status_label = '비활성';
                $row->status_color = 'gray';
            }

            // 타입 라벨
            $typeLabels = [
                'email' => '이메일',
                'ip' => 'IP 주소',
                'phone' => '전화번호',
                'domain' => '도메인',
                'user_agent' => 'User Agent',
                'account' => '계정'
            ];
            $row->type_label = $typeLabels[$row->type] ?? $row->type;

            // 영구 차단 여부
            $row->is_permanent = !$row->expires_at;
        }

        return $rows;
    }

    /**
     * 일괄 차단 추가
     */
    public function bulkAdd(Request $request)
    {
        $request->validate([
            'type' => 'required|in:email,ip,phone,domain,user_agent,account',
            'values' => 'required|string',
            'reason' => 'required|string|max:255',
            'description' => 'nullable|string',
            'expires_at' => 'nullable|date',
            'is_permanent' => 'boolean'
        ]);

        $values = array_filter(array_map('trim', explode("\n", $request->values)));
        $successCount = 0;
        $failedValues = [];

        DB::beginTransaction();
        try {
            foreach ($values as $value) {
                // 유효성 검사
                if (!$this->validateValue($request->type, $value)) {
                    $failedValues[] = $value;
                    continue;
                }

                // 중복 체크
                $exists = Blacklist::where('type', $request->type)
                    ->where('value', $value)
                    ->exists();

                if (!$exists) {
                    Blacklist::create([
                        'type' => $request->type,
                        'value' => $this->normalizeValue($request->type, $value),
                        'reason' => $request->reason,
                        'description' => $request->description,
                        'added_by' => auth()->user()->id ?? null,
                        'expires_at' => $request->is_permanent ? null : $request->expires_at,
                        'is_active' => true,
                        'meta' => [
                            'bulk_added' => true,
                            'added_at' => now()->toIsoString(),
                            'user_ip' => request()->ip()
                        ]
                    ]);
                    $successCount++;
                }
            }

            DB::commit();

            $message = "{$successCount}개 항목이 블랙리스트에 추가되었습니다.";
            if (!empty($failedValues)) {
                $message .= " (실패: " . implode(', ', $failedValues) . ")";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'added_count' => $successCount,
                'failed_values' => $failedValues
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '처리 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 일괄 차단 해제
     */
    public function bulkRemove(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:blacklists,id'
        ]);

        $count = Blacklist::whereIn('id', $request->ids)
            ->update(['is_active' => false]);

        return response()->json([
            'success' => true,
            'message' => "{$count}개 항목이 비활성화되었습니다."
        ]);
    }

    /**
     * 화이트리스트 관리
     */
    public function whitelist()
    {
        // 화이트리스트는 별도 테이블로 관리하거나
        // 블랙리스트의 메타데이터로 관리할 수 있음
        $this->jsonData['template']['index'] = 'jiny-auth::admin.auth_blacklist.whitelist';
        
        return view('jiny-admin::crud.index', [
            'jsonData' => $this->jsonData
        ]);
    }

    /**
     * 차단 여부 확인 API
     */
    public function check(Request $request)
    {
        $request->validate([
            'type' => 'required|in:email,ip,phone,domain,user_agent,account',
            'value' => 'required|string'
        ]);

        $normalizedValue = $this->normalizeValue($request->type, $request->value);
        $isBlacklisted = false;
        $entry = null;

        switch ($request->type) {
            case 'email':
                $isBlacklisted = Blacklist::isEmailBlacklisted($normalizedValue);
                break;
            case 'ip':
                $isBlacklisted = Blacklist::isIpBlacklisted($normalizedValue);
                break;
            case 'phone':
                $isBlacklisted = Blacklist::isPhoneBlacklisted($normalizedValue);
                break;
            default:
                $isBlacklisted = Blacklist::isBlacklisted($request->type, $normalizedValue);
        }

        if ($isBlacklisted) {
            $entry = Blacklist::active()
                ->where('type', $request->type)
                ->where('value', $normalizedValue)
                ->first();
        }

        return response()->json([
            'is_blacklisted' => $isBlacklisted,
            'entry' => $entry ? [
                'id' => $entry->id,
                'type' => $entry->type,
                'value' => $entry->value,
                'reason' => $entry->reason,
                'expires_at' => $entry->expires_at,
                'is_permanent' => !$entry->expires_at
            ] : null
        ]);
    }

    /**
     * Hook: 검색 구성
     */
    public function hookSearch($wire)
    {
        return [
            'fields' => ['type', 'value', 'reason', 'description'],
            'placeholder' => '차단 대상, 사유로 검색...'
        ];
    }

    /**
     * Hook: 필터 구성
     */
    public function hookFilters($wire)
    {
        return [
            'type' => [
                'label' => '차단 유형',
                'options' => [
                    '' => '전체',
                    'email' => '이메일',
                    'ip' => 'IP 주소',
                    'phone' => '전화번호',
                    'domain' => '도메인',
                    'user_agent' => 'User Agent',
                    'account' => '계정'
                ]
            ],
            'status' => [
                'label' => '상태',
                'options' => [
                    '' => '전체',
                    'active' => '활성',
                    'inactive' => '비활성',
                    'expired' => '만료됨'
                ]
            ],
            'is_permanent' => [
                'label' => '차단 기간',
                'options' => [
                    '' => '전체',
                    '1' => '영구 차단',
                    '0' => '임시 차단'
                ]
            ]
        ];
    }

    /**
     * 값 유효성 검사
     */
    protected function validateValue($type, $value)
    {
        switch ($type) {
            case 'email':
                return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
            case 'ip':
                return filter_var($value, FILTER_VALIDATE_IP) !== false;
            case 'phone':
                // 전화번호 형식 검증 (숫자와 일부 특수문자만 허용)
                return preg_match('/^[\d\+\-\(\)\s]+$/', $value);
            case 'domain':
                return filter_var($value, FILTER_VALIDATE_DOMAIN) !== false;
            default:
                return !empty($value);
        }
    }

    /**
     * 값 정규화
     */
    protected function normalizeValue($type, $value)
    {
        switch ($type) {
            case 'email':
                return strtolower(trim($value));
            case 'ip':
                return trim($value);
            case 'phone':
                // 전화번호에서 특수문자 제거
                return preg_replace('/[^\d\+]/', '', $value);
            case 'domain':
                return strtolower(trim($value));
            default:
                return trim($value);
        }
    }

    /**
     * Hook: 대량 작업 구성
     */
    public function hookBulkActions($wire)
    {
        return [
            'deactivate' => [
                'label' => '선택 항목 비활성화',
                'icon' => 'ban',
                'class' => 'text-yellow-600',
                'confirm' => '선택한 항목을 비활성화하시겠습니까?'
            ],
            'activate' => [
                'label' => '선택 항목 활성화',
                'icon' => 'check',
                'class' => 'text-green-600',
                'confirm' => '선택한 항목을 활성화하시겠습니까?'
            ],
            'delete' => [
                'label' => '선택 항목 삭제',
                'icon' => 'trash',
                'class' => 'text-red-600',
                'confirm' => '선택한 항목을 영구 삭제하시겠습니까?'
            ]
        ];
    }
}