<?php

namespace Jiny\Auth\App\Http\Controllers\Admin\AuthTwoFactor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\App\Models\TwoFactorAuth;
use Illuminate\Support\Str;

class AuthTwoFactorEdit extends Controller
{
    private $jsonData;

    public function __construct()
    {
        $this->jsonData = $this->loadJsonData();
    }

    private function loadJsonData()
    {
        $jsonPath = __DIR__ . '/AuthTwoFactor.json';
        if (file_exists($jsonPath)) {
            $jsonContent = file_get_contents($jsonPath);
            return json_decode($jsonContent, true);
        }
        return [];
    }

    public function index($id)
    {
        $data = TwoFactorAuth::with('account')->findOrFail($id);
        
        // JSON 설정에 컨트롤러 클래스 추가
        $this->jsonData['controllerClass'] = self::class;
        
        // 템플릿 경로 설정
        $this->jsonData['template'] = [
            'edit' => 'jiny-admin::crud.edit',
            'form' => 'jiny-auth::admin.auth_two_factor.edit'
        ];

        return view('jiny-admin::crud.edit', [
            'jsonData' => $this->jsonData,
            'data' => $data,
            'id' => $id
        ]);
    }

    /**
     * Hook: 편집 폼 로드 시
     */
    public function hookEditing($livewire, $form)
    {
        // 복구 코드는 보안상 표시하지 않음
        if (isset($form['recovery_codes'])) {
            $codes = json_decode($form['recovery_codes'], true);
            if (is_array($codes)) {
                $form['recovery_codes_count'] = count($codes);
                $form['recovery_codes_display'] = '복구 코드 ' . count($codes) . '개 설정됨';
            }
            unset($form['recovery_codes']); // 실제 코드는 제거
        }
        
        // 비밀키도 마스킹 처리
        if (isset($form['secret'])) {
            $form['secret_masked'] = substr($form['secret'], 0, 4) . '****' . substr($form['secret'], -4);
            unset($form['secret']);
        }
        
        return $form;
    }

    /**
     * Hook: 업데이트 전 처리
     */
    public function hookUpdating($livewire, $form)
    {
        // 방법 변경 시 secret 재생성
        if (isset($form['method']) && $form['method'] !== $livewire->originalData['method']) {
            if ($form['method'] === 'totp') {
                // TOTP용 새 시크릿 생성
                $form['secret'] = $this->generateBase32Secret();
            }
        }
        
        // 활성화 상태 변경 시 타임스탬프 업데이트
        if (isset($form['enabled'])) {
            if ($form['enabled'] && !$livewire->originalData['enabled']) {
                $form['enabled_at'] = now();
                $form['failed_attempts'] = 0; // 실패 시도 초기화
            } elseif (!$form['enabled'] && $livewire->originalData['enabled']) {
                $form['enabled_at'] = null;
            }
        }
        
        return $form;
    }

    /**
     * Hook: 업데이트 후 처리
     */
    public function hookUpdated($livewire, $model)
    {
        // 활동 로그 기록
        activity()
            ->performedOn($model)
            ->withProperties(['changes' => $model->getChanges()])
            ->log('2FA 설정 수정');
            
        // 사용자에게 알림 전송 (필요시)
        // TODO: 이메일 또는 알림 전송 로직
    }

    /**
     * 복구 코드 재생성
     */
    public function resetRecoveryCodes($id)
    {
        $twoFactor = TwoFactorAuth::findOrFail($id);
        
        // 10개의 복구 코드 생성
        $codes = [];
        for ($i = 0; $i < 10; $i++) {
            $codes[] = strtoupper(Str::random(8) . '-' . Str::random(8));
        }
        
        $twoFactor->recovery_codes = json_encode($codes);
        $twoFactor->save();
        
        // 활동 로그
        activity()
            ->performedOn($twoFactor)
            ->log('복구 코드 재생성');
        
        return response()->json([
            'success' => true,
            'message' => '복구 코드가 재생성되었습니다.',
            'codes' => $codes // 관리자에게 한 번만 표시
        ]);
    }

    /**
     * Hook: 복구 코드 재생성 처리
     */
    public function hookResetCodes($livewire, $id)
    {
        $result = $this->resetRecoveryCodes($id);
        $data = json_decode($result->getContent(), true);
        
        if ($data['success']) {
            session()->flash('success', $data['message']);
            
            // 생성된 코드를 임시로 세션에 저장 (한 번만 표시하기 위해)
            session()->flash('recovery_codes', $data['codes']);
        }
        
        return $data;
    }

    /**
     * Base32 시크릿 생성 (TOTP용)
     */
    private function generateBase32Secret($length = 16)
    {
        $base32 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';
        
        for ($i = 0; $i < $length; $i++) {
            $secret .= $base32[random_int(0, 31)];
        }
        
        return $secret;
    }
}