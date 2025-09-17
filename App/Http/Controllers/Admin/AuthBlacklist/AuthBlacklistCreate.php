<?php

namespace Jiny\Auth\App\Http\Controllers\Admin\AuthBlacklist;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Jiny\Auth\App\Models\Blacklist;

class AuthBlacklistCreate extends Controller
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

    public function index()
    {
        $this->jsonData['route'] = [
            'name' => 'admin.auth.blacklist',
            'create' => 'admin.auth.blacklist.create',
            'store' => 'admin.auth.blacklist.store'
        ];

        $this->jsonData['template'] = [
            'create' => 'jiny-auth::admin.auth_blacklist.create'
        ];

        // controllerClass 설정 (Hook 시스템 활성화)
        $this->jsonData['controllerClass'] = self::class;

        return view('jiny-admin::crud.create', [
            'jsonData' => $this->jsonData
        ]);
    }

    /**
     * Hook: 생성 폼 초기화
     */
    public function hookCreating($wire, $value)
    {
        // 기본값 설정
        $value['is_active'] = true;
        $value['is_permanent'] = false;
        $value['type'] = 'email'; // 기본 타입
        
        // 현재 사용자 정보 설정
        if (auth()->check()) {
            $value['blocked_by'] = auth()->user()->id;
            $value['blocked_by_name'] = auth()->user()->name ?? auth()->user()->email;
        }

        return $value;
    }

    /**
     * Hook: 저장 전 유효성 검사
     */
    public function hookValidating($wire, $form)
    {
        $errors = [];

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

        // 중복 체크
        $exists = Blacklist::where('type', $form['type'])
            ->where('value', $this->normalizeValue($form['type'], $form['value']))
            ->first();
        
        if ($exists) {
            if ($exists->is_active) {
                $errors['value'] = '이미 블랙리스트에 등록되어 있습니다.';
            } else {
                // 비활성 상태인 경우 재활성화 안내
                $wire->dispatch('confirm-reactivate', ['id' => $exists->id]);
                return "비활성 상태의 동일한 항목이 존재합니다. 재활성화하시겠습니까?";
            }
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
     * Hook: 저장 전 데이터 처리
     */
    public function hookStoring($wire, $form)
    {
        // 값 정규화
        $form['value'] = $this->normalizeValue($form['type'], $form['value']);
        
        // 차단자 정보 추가
        if (auth()->check()) {
            $form['added_by'] = auth()->user()->id;
        }

        // 영구 차단 설정
        if (isset($form['is_permanent']) && $form['is_permanent']) {
            $form['expires_at'] = null;
        } elseif (empty($form['expires_at'])) {
            // 기본 만료 기간 설정 (30일)
            $form['expires_at'] = now()->addDays(30);
        }

        // 메타데이터 추가
        $form['meta'] = [
            'created_via' => 'admin_panel',
            'user_ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now()->toIsoString()
        ];

        // 설명이 비어있으면 기본 설명 추가
        if (empty($form['description'])) {
            $form['description'] = "관리자 패널에서 추가됨 - " . now()->format('Y-m-d H:i:s');
        }

        // 활동 로그 기록
        Log::info('Blacklist entry created', [
            'type' => $form['type'],
            'value' => $form['value'],
            'reason' => $form['reason'],
            'added_by' => $form['added_by'] ?? 'system'
        ]);

        return $form;
    }

    /**
     * Hook: 저장 후 처리
     */
    public function hookStored($wire, $model)
    {
        // 성공 메시지
        session()->flash('message', '블랙리스트에 성공적으로 추가되었습니다.');
        
        // 관련 캐시 클리어 (있다면)
        // Cache::forget('blacklist_' . $model->type);
        
        // 알림 전송 (필요시)
        // Notification::send($admins, new BlacklistEntryAdded($model));
    }

    /**
     * Hook: 폼 필드 구성
     */
    public function hookFormFields($wire)
    {
        return [
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
                'help' => '차단할 대상의 유형을 선택하세요.'
            ],
            'value' => [
                'label' => '차단 대상',
                'type' => 'text',
                'required' => true,
                'placeholder' => '차단할 값을 입력하세요',
                'help' => '차단할 이메일, IP, 전화번호 등을 입력하세요.'
            ],
            'reason' => [
                'label' => '차단 사유',
                'type' => 'text',
                'required' => true,
                'placeholder' => '차단 사유를 간단히 입력하세요',
                'maxlength' => 255
            ],
            'description' => [
                'label' => '상세 설명',
                'type' => 'textarea',
                'rows' => 3,
                'placeholder' => '차단에 대한 상세 설명을 입력하세요 (선택사항)'
            ],
            'is_permanent' => [
                'label' => '영구 차단',
                'type' => 'checkbox',
                'help' => '체크하면 만료 기간 없이 영구 차단됩니다.'
            ],
            'expires_at' => [
                'label' => '만료 일시',
                'type' => 'datetime-local',
                'help' => '영구 차단이 아닌 경우 만료 일시를 설정하세요.',
                'depends_on' => [
                    'field' => 'is_permanent',
                    'value' => false
                ]
            ]
        ];
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
}