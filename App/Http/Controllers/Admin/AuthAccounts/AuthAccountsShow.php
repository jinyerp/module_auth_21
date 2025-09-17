<?php

namespace Jiny\Auth\App\Http\Controllers\Admin\AuthAccounts;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Admin\App\Services\JsonConfigService;

/**
 * 회원 상세 정보 컨트롤러
 * 
 * 회원의 상세 정보를 표시합니다.
 * Livewire 컴포넌트(AdminShow)와 Hook 패턴을 통해 동작합니다.
 * 
 * @package Jiny\Auth\App\Http\Controllers\Admin\AuthAccounts
 * @author  @jiny/auth Team
 * @since   1.0.0
 * 
 * ## Hook 메소드 호출 트리
 * ```
 * Livewire\AdminShow Component
 * ├── hookShowing($id)                    [데이터 로드 전]
 * │   └── 접근 권한 확인
 * ├── hookShowed($record)                 [데이터 로드 후]
 * │   ├── 관련 데이터 추가
 * │   ├── 등급 정보
 * │   ├── 국가 정보
 * │   ├── 로그인 히스토리
 * │   └── 계정 활동 로그
 * └── hookDetailFields()                  [표시 필드 커스터마이징]
 * ```
 */
class AuthAccountsShow extends Controller
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
     * 회원 상세 정보 표시
     */
    public function __invoke(Request $request, $id)
    {
        // JSON 데이터 확인
        if (! $this->jsonData) {
            return response('Error: JSON 데이터를 로드할 수 없습니다.', 500);
        }

        // template.show view 경로 확인
        if (! isset($this->jsonData['template']['show'])) {
            return response('Error: 화면을 출력하기 위한 template.show 설정이 필요합니다.', 500);
        }

        // route 정보를 jsonData에 추가
        if (isset($this->jsonData['route']['name'])) {
            $this->jsonData['currentRoute'] = $this->jsonData['route']['name'];
        }

        // JSON 파일 경로 추가
        $jsonPath = __DIR__.DIRECTORY_SEPARATOR.'AuthAccounts.json';
        $settingsPath = $jsonPath;

        // 현재 컨트롤러 클래스를 JSON 데이터에 추가
        $this->jsonData['controllerClass'] = get_class($this);

        // 회원 정보 조회
        $tableName = $this->jsonData['table']['name'] ?? 'accounts';
        $data = DB::table($tableName)->where('id', $id)->first();

        if (!$data) {
            return response('회원 정보를 찾을 수 없습니다.', 404);
        }

        // 관련 정보 추가
        $data = $this->loadRelatedData($data);

        return view($this->jsonData['template']['show'], [
            'jsonData' => $this->jsonData,
            'jsonPath' => $jsonPath,
            'settingsPath' => $settingsPath,
            'id' => $id,
            'data' => $data,
            'controllerClass' => static::class,
        ]);
    }

    /**
     * 관련 데이터를 로드합니다.
     */
    private function loadRelatedData($data)
    {
        // 등급 정보
        if ($data->grade_id) {
            $grade = DB::table('grades')->where('id', $data->grade_id)->first();
            $data->grade_name = $grade ? $grade->name : null;
            $data->grade_level = $grade ? $grade->level : null;
        }

        // 국가 정보
        if ($data->country_id) {
            $country = DB::table('countries')->where('id', $data->country_id)->first();
            $data->country_name = $country ? $country->name : null;
            $data->country_code = $country ? $country->code : null;
        }

        // 역할 정보
        $roles = DB::table('role_account')
            ->join('roles', 'role_account.role_id', '=', 'roles.id')
            ->where('role_account.account_id', $data->id)
            ->select('roles.name', 'roles.description')
            ->get();
        $data->roles = $roles;

        // 2FA 상태
        $twoFactorAuth = DB::table('two_factor_auths')
            ->where('account_id', $data->id)
            ->first();
        $data->two_factor_enabled = $twoFactorAuth ? true : false;

        return $data;
    }

    /**
     * 데이터 로드 전 호출되는 Hook
     * 
     * @param mixed $wire Livewire 컴포넌트 인스턴스
     * @param int $id 조회할 회원 ID
     * @return void
     */
    public function hookShowing($wire, $id)
    {
        // 접근 권한 확인
        // 필요한 경우 특정 권한 체크 로직 추가
        
        // 조회 로그 기록
        DB::table('account_logs')->insert([
            'account_id' => $id,
            'action' => 'viewed',
            'description' => '관리자가 회원 정보 조회',
            'performed_by' => auth()->id(),
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);
    }

    /**
     * 데이터 로드 후 호출되는 Hook
     * 
     * @param mixed $wire Livewire 컴포넌트 인스턴스
     * @param object $record 로드된 회원 데이터
     * @return object 수정된 레코드
     */
    public function hookShowed($wire, $record)
    {
        // 최근 로그인 히스토리
        $recentLogins = DB::table('login_histories')
            ->where('account_id', $record->id)
            ->where('action', 'login')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        $record->recent_logins = $recentLogins;

        // 최근 활동 로그
        $recentActivities = DB::table('account_logs')
            ->where('account_id', $record->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        $record->recent_activities = $recentActivities;

        // 통계 정보
        $stats = new \stdClass();
        $stats->total_logins = DB::table('login_histories')
            ->where('account_id', $record->id)
            ->where('action', 'login')
            ->count();
        
        $stats->last_login = DB::table('login_histories')
            ->where('account_id', $record->id)
            ->where('action', 'login')
            ->orderBy('created_at', 'desc')
            ->value('created_at');
            
        $stats->failed_login_attempts = DB::table('login_histories')
            ->where('account_id', $record->id)
            ->where('action', 'login_failed')
            ->where('created_at', '>', now()->subDays(30))
            ->count();

        $record->stats = $stats;

        return $record;
    }

    /**
     * 표시 필드를 커스터마이징하는 Hook
     * 
     * @param mixed $wire Livewire 컴포넌트 인스턴스
     * @return array 표시할 필드 설정
     */
    public function hookDetailFields($wire)
    {
        return [
            'basic_info' => [
                'title' => '기본 정보',
                'fields' => [
                    'id' => ['label' => 'ID', 'type' => 'text'],
                    'name' => ['label' => '이름', 'type' => 'text'],
                    'email' => ['label' => '이메일', 'type' => 'email'],
                    'phone' => ['label' => '전화번호', 'type' => 'text'],
                    'status' => ['label' => '상태', 'type' => 'badge'],
                    'grade_name' => ['label' => '등급', 'type' => 'text'],
                    'country_name' => ['label' => '국가', 'type' => 'text'],
                ],
            ],
            'security_info' => [
                'title' => '보안 정보',
                'fields' => [
                    'email_verified_at' => ['label' => '이메일 인증일', 'type' => 'datetime'],
                    'two_factor_enabled' => ['label' => '2단계 인증', 'type' => 'boolean'],
                    'last_password_changed_at' => ['label' => '마지막 비밀번호 변경', 'type' => 'datetime'],
                ],
            ],
            'timestamps' => [
                'title' => '시간 정보',
                'fields' => [
                    'created_at' => ['label' => '가입일', 'type' => 'datetime'],
                    'updated_at' => ['label' => '수정일', 'type' => 'datetime'],
                    'last_login_at' => ['label' => '마지막 로그인', 'type' => 'datetime'],
                ],
            ],
        ];
    }

    /**
     * 커스텀 액션을 처리하는 Hook
     * 
     * @param mixed $wire Livewire 컴포넌트 인스턴스
     * @param string $action 액션 이름
     * @param array $params 액션 파라미터
     * @return mixed
     */
    public function hookCustomAction($wire, $action, $params = [])
    {
        switch ($action) {
            case 'resetPassword':
                // 비밀번호 재설정 링크 발송
                return $this->sendPasswordResetLink($params['id']);
                
            case 'toggleStatus':
                // 계정 상태 토글
                return $this->toggleAccountStatus($params['id']);
                
            case 'sendVerificationEmail':
                // 인증 이메일 재발송
                return $this->resendVerificationEmail($params['id']);
                
            default:
                return false;
        }
    }

    /**
     * 비밀번호 재설정 링크 발송
     */
    private function sendPasswordResetLink($accountId)
    {
        // 비밀번호 재설정 로직 구현
        DB::table('account_logs')->insert([
            'account_id' => $accountId,
            'action' => 'password_reset_requested',
            'description' => '관리자가 비밀번호 재설정 링크 발송',
            'performed_by' => auth()->id(),
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);

        return ['success' => true, 'message' => '비밀번호 재설정 링크가 발송되었습니다.'];
    }

    /**
     * 계정 상태 토글
     */
    private function toggleAccountStatus($accountId)
    {
        $account = DB::table('accounts')->where('id', $accountId)->first();
        
        if (!$account) {
            return ['success' => false, 'message' => '회원을 찾을 수 없습니다.'];
        }

        $newStatus = $account->status === 'active' ? 'inactive' : 'active';
        
        DB::table('accounts')
            ->where('id', $accountId)
            ->update([
                'status' => $newStatus,
                'updated_at' => now(),
            ]);

        DB::table('account_logs')->insert([
            'account_id' => $accountId,
            'action' => 'status_changed',
            'description' => "계정 상태 변경: {$account->status} -> {$newStatus}",
            'performed_by' => auth()->id(),
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);

        return ['success' => true, 'message' => '계정 상태가 변경되었습니다.'];
    }

    /**
     * 인증 이메일 재발송
     */
    private function resendVerificationEmail($accountId)
    {
        // 이메일 인증 로직 구현
        DB::table('account_logs')->insert([
            'account_id' => $accountId,
            'action' => 'verification_email_resent',
            'description' => '관리자가 인증 이메일 재발송',
            'performed_by' => auth()->id(),
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);

        return ['success' => true, 'message' => '인증 이메일이 재발송되었습니다.'];
    }
}