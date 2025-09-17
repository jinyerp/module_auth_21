<?php

namespace Jiny\Auth\App\Http\Controllers\Admin\AuthBlacklist;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Jiny\Auth\App\Models\Blacklist;

class AuthBlacklistEdit extends Controller
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
            'edit' => 'admin.auth.blacklist.edit',
            'update' => 'admin.auth.blacklist.update'
        ];

        $this->jsonData['template'] = [
            'edit' => 'jiny-auth::admin.auth_blacklist.edit'
        ];

        // controllerClass 설정 (Hook 시스템 활성화)
        $this->jsonData['controllerClass'] = self::class;

        return view('jiny-admin::crud.edit', [
            'jsonData' => $this->jsonData,
            'data' => $data,
            'id' => $id
        ]);
    }

    /**
     * Hook: 편집 폼 로드
     */
    public function hookEditing($wire, $model)
    {
        // 관계 데이터 로드
        if ($model->added_by) {
            $model->load('addedBy');
        }
        
        // 영구 차단 여부 설정
        $model->is_permanent = !$model->expires_at;
        
        // 메타데이터 파싱
        if ($model->meta) {
            $model->meta_parsed = is_string($model->meta) ? json_decode($model->meta, true) : $model->meta;
        }

        return $model;
    }

    /**
     * Hook: 업데이트 전 유효성 검사
     */
    public function hookValidating($wire, $form)
    {
        $errors = [];
        $id = $wire->modelId;

        // 타입과 값 변경 체크
        $original = Blacklist::find($id);
        if ($original) {
            $typeChanged = $original->type !== $form['type'];
            $valueChanged = $original->value !== $form['value'];

            if ($typeChanged || $valueChanged) {
                // 중복 체크
                $exists = Blacklist::where('type', $form['type'])
                    ->where('value', $this->normalizeValue($form['type'], $form['value']))
                    ->where('id', '!=', $id)
                    ->first();
                
                if ($exists) {
                    $errors['value'] = '이미 블랙리스트에 등록되어 있습니다.';
                }
            }
        }

        // 타입별 유효성 검사
        switch ($form['type']) {
            case 'email':
                if (!filter_var($form['value'], FILTER_VALIDATE_EMAIL)) {
                    $errors['value'] = '올바른 이메일 형식이 아닙니다.';
                }
                break;
            case 'ip':
                if (!filter_var($form['value'], FILTER_VALIDATE_IP)) {
                    $errors['value'] = '올바른 IP 주소 형식이 아닙니다.';
                }
                break;
            case 'phone':
                if (!preg_match('/^[\d\+\-\(\)\s]+$/', $form['value'])) {
                    $errors['value'] = '올바른 전화번호 형식이 아닙니다.';
                }
                break;
            case 'domain':
                if (!filter_var($form['value'], FILTER_VALIDATE_DOMAIN)) {
                    $errors['value'] = '올바른 도메인 형식이 아닙니다.';
                }
                break;
        }

        if (!empty($errors)) {
            foreach ($errors as $field => $message) {
                $wire->addError('form.' . $field, $message);
            }
            return false;
        }

        return $form;
    }

    /**
     * Hook: 업데이트 전 데이터 처리
     */
    public function hookUpdating($wire, $form)
    {
        $id = $wire->modelId;
        $original = Blacklist::find($id);

        // 값 정규화
        $form['value'] = $this->normalizeValue($form['type'], $form['value']);
        
        // 영구 차단 설정
        if (isset($form['is_permanent']) && $form['is_permanent']) {
            $form['expires_at'] = null;
        } elseif (empty($form['expires_at']) && !$form['is_permanent']) {
            // 기존 만료 기간 유지
            $form['expires_at'] = $original->expires_at;
        }

        // 메타데이터 업데이트
        $meta = $original->meta ?? [];
        $meta['last_modified_at'] = now()->toIsoString();
        $meta['last_modified_by'] = auth()->user()->id ?? 'system';
        $meta['modification_ip'] = request()->ip();
        
        // 변경 이력 추가
        if (!isset($meta['modification_history'])) {
            $meta['modification_history'] = [];
        }
        
        $meta['modification_history'][] = [
            'date' => now()->toIsoString(),
            'user' => auth()->user()->name ?? 'Unknown',
            'changes' => $this->getChanges($original, $form)
        ];
        
        $form['meta'] = $meta;

        // 활동 로그 기록
        Log::info('Blacklist entry updated', [
            'id' => $id,
            'type' => $form['type'],
            'value' => $form['value'],
            'modified_by' => auth()->user()->id ?? 'system',
            'changes' => $this->getChanges($original, $form)
        ]);

        return $form;
    }

    /**
     * Hook: 업데이트 후 처리
     */
    public function hookUpdated($wire, $model)
    {
        // 성공 메시지
        session()->flash('message', '블랙리스트 항목이 성공적으로 수정되었습니다.');
        
        // 관련 캐시 클리어 (있다면)
        // Cache::forget('blacklist_' . $model->type);
        
        // 중요한 변경사항 알림 (필요시)
        // if ($model->wasChanged(['is_active', 'expires_at'])) {
        //     Notification::send($admins, new BlacklistEntryModified($model));
        // }
    }

    /**
     * Hook: 편집 폼 필드 구성
     */
    public function hookFormFields($wire, $model)
    {
        $fields = [
            'type' => [
                'label' => '차단 유형',
                'type' => 'select',
                'options' => [
                    'email' => '이메일',
                    'ip' => 'IP 주소',
                    'phone' => '전화번호',
                    'domain' => '도메인',
                    'user_agent' => 'User Agent',
                    'account' => '계정'
                ],
                'required' => true,
                'value' => $model->type
            ],
            'value' => [
                'label' => '차단 대상',
                'type' => 'text',
                'required' => true,
                'value' => $model->value
            ],
            'reason' => [
                'label' => '차단 사유',
                'type' => 'text',
                'required' => true,
                'value' => $model->reason
            ],
            'description' => [
                'label' => '상세 설명',
                'type' => 'textarea',
                'rows' => 3,
                'value' => $model->description
            ],
            'is_active' => [
                'label' => '활성화',
                'type' => 'checkbox',
                'value' => $model->is_active,
                'help' => '체크 해제하면 차단이 비활성화됩니다.'
            ],
            'is_permanent' => [
                'label' => '영구 차단',
                'type' => 'checkbox',
                'value' => !$model->expires_at,
                'help' => '체크하면 만료 기간 없이 영구 차단됩니다.'
            ],
            'expires_at' => [
                'label' => '만료 일시',
                'type' => 'datetime-local',
                'value' => $model->expires_at ? $model->expires_at->format('Y-m-d\TH:i') : '',
                'depends_on' => [
                    'field' => 'is_permanent',
                    'value' => false
                ]
            ]
        ];

        // 통계 정보 표시 (읽기 전용)
        if ($model->hit_count > 0) {
            $fields['statistics'] = [
                'label' => '차단 통계',
                'type' => 'display',
                'value' => "차단 횟수: {$model->hit_count}회, 마지막 차단: " . 
                          ($model->last_hit_at ? $model->last_hit_at->format('Y-m-d H:i:s') : 'N/A')
            ];
        }

        return $fields;
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
                return preg_replace('/[^\d\+]/', '', $value);
            case 'domain':
                return strtolower(trim($value));
            default:
                return trim($value);
        }
    }

    /**
     * 변경사항 추출
     */
    protected function getChanges($original, $new)
    {
        $changes = [];
        $fields = ['type', 'value', 'reason', 'description', 'is_active', 'expires_at'];
        
        foreach ($fields as $field) {
            if (isset($new[$field]) && $original->$field != $new[$field]) {
                $changes[$field] = [
                    'from' => $original->$field,
                    'to' => $new[$field]
                ];
            }
        }
        
        return $changes;
    }
}