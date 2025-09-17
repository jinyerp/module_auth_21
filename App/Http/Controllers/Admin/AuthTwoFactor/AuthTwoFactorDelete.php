<?php

namespace Jiny\Auth\App\Http\Controllers\Admin\AuthTwoFactor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\App\Models\TwoFactorAuth;

class AuthTwoFactorDelete extends Controller
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
            'delete' => 'jiny-admin::crud.delete',
            'confirm' => 'jiny-auth::admin.auth_two_factor.disable'
        ];
        
        // 비활성화 확인 메시지 설정
        $this->jsonData['delete'] = [
            'title' => '2단계 인증 비활성화',
            'message' => '이 사용자의 2단계 인증을 비활성화하시겠습니까?',
            'warning' => '비활성화 후 사용자는 2단계 인증 없이 로그인할 수 있습니다.',
            'button' => '비활성화',
            'buttonClass' => 'bg-yellow-600 hover:bg-yellow-700'
        ];

        return view('jiny-admin::crud.delete', [
            'jsonData' => $this->jsonData,
            'data' => $data,
            'id' => $id
        ]);
    }

    /**
     * Hook: 비활성화 전 처리
     */
    public function hookDeleting($livewire, $id)
    {
        $twoFactor = TwoFactorAuth::find($id);
        
        if (!$twoFactor) {
            return "2FA 설정을 찾을 수 없습니다.";
        }
        
        // 이미 비활성화된 경우
        if (!$twoFactor->enabled) {
            return "이미 비활성화된 2FA입니다.";
        }
        
        // 관리자 권한 체크 (필요시)
        if (!auth()->user()->hasRole('super-admin')) {
            // return "Super Admin 권한이 필요합니다.";
        }
        
        return true; // 비활성화 허용
    }

    /**
     * Hook: 실제 삭제 대신 비활성화 처리
     */
    public function hookDisabling($livewire, $id)
    {
        $twoFactor = TwoFactorAuth::findOrFail($id);
        
        // 비활성화 처리
        $twoFactor->enabled = false;
        $twoFactor->enabled_at = null;
        $twoFactor->failed_attempts = 0;
        $twoFactor->save();
        
        // 활동 로그 기록
        activity()
            ->performedOn($twoFactor)
            ->causedBy(auth()->user())
            ->withProperties([
                'account_id' => $twoFactor->account_id,
                'method' => $twoFactor->method,
                'admin_action' => true
            ])
            ->log('관리자가 2FA 비활성화');
        
        // 사용자에게 알림 (선택사항)
        // $this->notifyUserOf2FADisabled($twoFactor);
        
        session()->flash('success', '2단계 인증이 비활성화되었습니다.');
        
        return true;
    }

    /**
     * Hook: 비활성화 후 처리
     */
    public function hookDeleted($livewire, $id)
    {
        // 추가 정리 작업이 필요한 경우
        // 예: 캐시 클리어, 세션 종료 등
        
        // 비활성화 통계 업데이트
        cache()->forget('two_factor_stats');
        
        // 리다이렉트 설정
        $livewire->redirectRoute = 'admin.auth.two_factor';
    }

    /**
     * 실패 시도 초기화
     */
    public function resetFailedAttempts($id)
    {
        $twoFactor = TwoFactorAuth::findOrFail($id);
        
        $twoFactor->failed_attempts = 0;
        $twoFactor->save();
        
        activity()
            ->performedOn($twoFactor)
            ->log('2FA 실패 시도 초기화');
        
        return response()->json([
            'success' => true,
            'message' => '실패 시도가 초기화되었습니다.'
        ]);
    }
}