<?php

namespace Jiny\Auth\App\Http\Controllers\Admin\AuthAccounts;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Admin\App\Services\JsonConfigService;

/**
 * 회원 삭제 컨트롤러
 * 
 * 회원 계정 삭제 처리를 담당합니다.
 * Livewire 컴포넌트(AdminDelete)와 Hook 패턴을 통해 동작합니다.
 * 
 * @package Jiny\Auth\App\Http\Controllers\Admin\AuthAccounts
 * @author  @jiny/auth Team
 * @since   1.0.0
 * 
 * ## Hook 메소드 호출 트리
 * ```
 * Livewire\AdminDelete Component
 * ├── hookDeleting($id)                   [삭제 전 처리]
 * │   ├── 삭제 가능 여부 확인
 * │   ├── 관련 데이터 확인
 * │   └── 휴면 계정으로 이동
 * └── hookDeleted($id)                    [삭제 후 처리]
 *     └── 로그 기록
 * ```
 */
class AuthAccountsDelete extends Controller
{
    private $jsonData;

    public function __construct()
    {
        // 서비스를 사용하여 JSON 파일 로드
        $jsonConfigService = new JsonConfigService;
        $this->jsonData = $jsonConfigService->loadFromControllerPath(__DIR__);
    }

    /**
     * Single Action __invoke method
     * 삭제 확인 페이지 표시
     */
    public function __invoke(Request $request)
    {
        $id = $request->route('id');
        
        // JSON 데이터 확인
        if (! $this->jsonData) {
            return response('Error: JSON 데이터를 로드할 수 없습니다.', 500);
        }

        // template 설정 확인 (삭제는 별도 템플릿 없이 모달로 처리)
        $template = $this->jsonData['template']['delete'] ?? 
                   $this->jsonData['template']['index'] ?? 
                   'jiny-admin::template.index';

        // route 정보를 jsonData에 추가
        if (isset($this->jsonData['route']['name'])) {
            $this->jsonData['currentRoute'] = $this->jsonData['route']['name'];
        }

        // JSON 파일 경로 추가
        $jsonPath = __DIR__.DIRECTORY_SEPARATOR.'AuthAccounts.json';

        // 현재 컨트롤러 클래스를 JSON 데이터에 추가
        $this->jsonData['controllerClass'] = get_class($this);

        // 회원 정보 조회
        $tableName = $this->jsonData['table']['name'] ?? 'accounts';
        $data = DB::table($tableName)->where('id', $id)->first();

        if (!$data) {
            return response('회원 정보를 찾을 수 없습니다.', 404);
        }

        return view($template, [
            'jsonData' => $this->jsonData,
            'jsonPath' => $jsonPath,
            'id' => $id,
            'data' => $data,
            'deleteMode' => true,
        ]);
    }

    /**
     * 삭제 전 호출되는 Hook
     * 
     * @param mixed $wire Livewire 컴포넌트 인스턴스
     * @param int $id 삭제할 회원 ID
     * @return bool|string false면 삭제 진행, 문자열이면 에러 메시지
     */
    public function hookDeleting($wire, $id)
    {
        // 회원 정보 조회
        $account = DB::table('accounts')->where('id', $id)->first();
        
        if (!$account) {
            return '삭제할 회원을 찾을 수 없습니다.';
        }

        // 관련 데이터 확인 (예: 활성 세션, 진행 중인 주문 등)
        $hasActiveSession = DB::table('login_histories')
            ->where('account_id', $id)
            ->where('action', 'login')
            ->where('created_at', '>', now()->subHours(24))
            ->exists();

        if ($hasActiveSession) {
            return '최근 24시간 내에 로그인한 회원은 삭제할 수 없습니다.';
        }

        // Soft Delete 방식: 휴면 계정으로 이동
        if ($this->jsonData['destroy']['enableSoftDelete'] ?? true) {
            // 휴면 계정 테이블로 데이터 이동
            DB::table('dormant_accounts')->insert([
                'account_id' => $account->id,
                'name' => $account->name,
                'email' => $account->email,
                'phone' => $account->phone,
                'data' => json_encode($account),
                'deleted_by' => auth()->id(),
                'deleted_reason' => '관리자 삭제',
                'created_at' => now(),
            ]);

            // 로그 기록
            DB::table('account_logs')->insert([
                'account_id' => $id,
                'action' => 'soft_deleted',
                'description' => '관리자에 의한 회원 삭제 (휴면 전환)',
                'performed_by' => auth()->id(),
                'ip_address' => request()->ip(),
                'created_at' => now(),
            ]);
        }

        // false 반환 시 삭제 진행
        return false;
    }

    /**
     * 삭제 후 호출되는 Hook
     * 
     * @param mixed $wire Livewire 컴포넌트 인스턴스
     * @param int $id 삭제된 회원 ID
     * @return void
     */
    public function hookDeleted($wire, $id)
    {
        // 관련 데이터 정리
        // 로그인 히스토리는 보관 (감사 목적)
        // 세션 데이터 삭제
        DB::table('sessions')
            ->where('user_id', $id)
            ->delete();

        // 2FA 데이터 삭제
        DB::table('two_factor_auths')
            ->where('account_id', $id)
            ->delete();

        // 역할 연결 삭제
        DB::table('role_account')
            ->where('account_id', $id)
            ->delete();

        // 삭제 완료 로그
        DB::table('account_logs')->insert([
            'account_id' => $id,
            'action' => 'deleted',
            'description' => '회원 계정 완전 삭제',
            'performed_by' => auth()->id(),
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);

        // 성공 메시지 설정
        if ($wire && method_exists($wire, 'dispatch')) {
            $wire->dispatch('notify', [
                'type' => 'success',
                'message' => '회원이 성공적으로 삭제되었습니다.'
            ]);
        }
    }

    /**
     * 삭제 가능 여부를 확인하는 Hook
     * 
     * @param mixed $wire Livewire 컴포넌트 인스턴스
     * @param int $id 확인할 회원 ID
     * @return bool true면 삭제 가능, false면 삭제 불가
     */
    public function hookCanDelete($wire, $id)
    {
        // 자기 자신은 삭제할 수 없음
        if (auth()->id() == $id) {
            if ($wire && method_exists($wire, 'addError')) {
                $wire->addError('delete', '자기 자신의 계정은 삭제할 수 없습니다.');
            }
            return false;
        }

        // 최고 관리자 계정은 삭제 불가
        $account = DB::table('accounts')->where('id', $id)->first();
        if ($account && $account->email === 'admin@admin.com') {
            if ($wire && method_exists($wire, 'addError')) {
                $wire->addError('delete', '최고 관리자 계정은 삭제할 수 없습니다.');
            }
            return false;
        }

        return true;
    }
}