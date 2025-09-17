<?php

namespace Jiny\Auth\App\Http\Controllers\Admin\AuthAccounts;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Jiny\Admin\App\Services\JsonConfigService;
use Jiny\Admin\App\Services\PasswordValidator;

/**
 * 회원 수정 컨트롤러
 * 
 * 회원 정보를 수정하는 폼 표시 및 처리를 담당합니다.
 * Livewire 컴포넌트(AdminEdit)와 Hook 패턴을 통해 동작합니다.
 * 
 * @package Jiny\Auth\App\Http\Controllers\Admin\AuthAccounts
 * @author  @jiny/auth Team
 * @since   1.0.0
 * 
 * ## Hook 메소드 호출 트리
 * ```
 * Livewire\AdminEdit Component
 * ├── hookEditing($form)                  [편집 폼 로드]
 * │   ├── grades 테이블 조회
 * │   ├── countries 테이블 조회
 * │   └── 기존 데이터 로드
 * ├── hookFormEmail($value)               [이메일 실시간 검증]
 * │   ├── 이메일 형식 검증
 * │   └── 중복 체크 (자신 제외)
 * ├── hookFormPassword($value)            [패스워드 실시간 검증]
 * │   └── PasswordValidator::validate()
 * ├── hookUpdating($form)                 [업데이트 전 처리]
 * │   ├── 패스워드 검증 (입력된 경우)
 * │   ├── Hash::make()
 * │   └── 타임스탬프 업데이트
 * └── hookUpdated($form)                  [업데이트 후 처리]
 * ```
 */
class AuthAccountsEdit extends Controller
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
     * 수정 폼 표시
     */
    public function __invoke(Request $request, $id)
    {
        // JSON 데이터 확인
        if (! $this->jsonData) {
            return response('Error: JSON 데이터를 로드할 수 없습니다.', 500);
        }

        // template.edit view 경로 확인
        if (! isset($this->jsonData['template']['edit'])) {
            return response('Error: 화면을 출력하기 위한 template.edit 설정이 필요합니다.', 500);
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

        return view($this->jsonData['template']['edit'], [
            'jsonData' => $this->jsonData,
            'jsonPath' => $jsonPath,
            'settingsPath' => $settingsPath,
            'id' => $id,
            'data' => $data,
        ]);
    }

    /**
     * 편집 폼이 로드될 때 호출됩니다.
     */
    public function hookEditing($wire, $form)
    {
        // 회원 등급 목록 가져오기
        $grades = DB::table('grades')
            ->orderBy('level', 'desc')
            ->get();
            
        // 국가 목록 가져오기
        $countries = DB::table('countries')
            ->orderBy('name', 'asc')
            ->get();

        // View에 전달할 데이터 설정
        if ($wire) {
            $wire->grades = $grades;
            $wire->countries = $countries;
        }

        // 패스워드 필드는 비워둠 (보안상의 이유)
        unset($form['password']);

        return $form;
    }

    /**
     * 이메일 필드 실시간 검증 (수정 시)
     */
    public function hookFormEmail($wire, $value, $fieldName = null)
    {
        if (!$value) {
            return;
        }

        // 이메일 형식 검증
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $wire->addError('form.email', '올바른 이메일 형식이 아닙니다.');
            return;
        }

        // 이메일 중복 체크 (자신 제외)
        $currentId = $wire->recordId ?? null;
        $query = DB::table('accounts')->where('email', $value);
        
        if ($currentId) {
            $query->where('id', '!=', $currentId);
        }

        if ($query->exists()) {
            $wire->addError('form.email', '이미 등록된 이메일 주소입니다.');
        }
    }

    /**
     * 패스워드 필드 실시간 검증 (수정 시)
     */
    public function hookFormPassword($wire, $value, $fieldName = null)
    {
        // 패스워드가 입력된 경우에만 검증
        if (!$value) {
            return;
        }

        $passwordValidator = new PasswordValidator;

        // 사용자 정보 준비 (유사성 체크용)
        $userData = [
            'name' => $wire->form['name'] ?? '',
            'email' => $wire->form['email'] ?? '',
        ];

        // 패스워드 유효성 검증
        if (!$passwordValidator->validate($value, $userData)) {
            $errors = $passwordValidator->getErrors();
            foreach ($errors as $error) {
                $wire->addError('form.password', $error);
            }
        }
    }

    /**
     * 패스워드 확인 필드 실시간 검증
     */
    public function hookFormPasswordConfirmation($wire, $value, $fieldName = null)
    {
        if (!$value) {
            return;
        }

        $password = $wire->form['password'] ?? '';
        if ($value && $password && $value !== $password) {
            $wire->addError('form.password_confirmation', '패스워드가 일치하지 않습니다.');
        }
    }

    /**
     * 데이터 업데이트 전에 호출됩니다.
     *
     * @return array|string 성공시 수정된 form 배열, 실패시 에러 메시지 문자열
     */
    public function hookUpdating($wire, $form)
    {
        // 패스워드가 입력된 경우에만 처리
        if (isset($form['password']) && $form['password']) {
            // 패스워드 확인 필드 검증
            if (isset($form['password_confirmation'])) {
                if ($form['password'] !== $form['password_confirmation']) {
                    $errorMessage = '패스워드와 패스워드 확인이 일치하지 않습니다.';

                    if ($wire && method_exists($wire, 'addError')) {
                        $wire->addError('form.password_confirmation', $errorMessage);
                    }

                    return $errorMessage;
                }
            }

            $passwordValidator = new PasswordValidator;

            // 사용자 정보 준비 (유사성 체크용)
            $userData = [
                'name' => $form['name'] ?? '',
                'email' => $form['email'] ?? '',
            ];

            // 패스워드 유효성 검증
            if (!$passwordValidator->validate($form['password'], $userData)) {
                $errors = $passwordValidator->getErrors();
                $errorMessage = '패스워드 검증 실패: '.implode(' ', $errors);

                if ($wire && method_exists($wire, 'addError')) {
                    foreach ($errors as $error) {
                        $wire->addError('form.password', $error);
                    }
                }

                return $errorMessage;
            }

            // 검증 통과 시 패스워드 해싱
            $form['password'] = Hash::make($form['password']);
        } else {
            // 패스워드가 비어있으면 업데이트하지 않음
            unset($form['password']);
        }

        // 불필요한 필드 제거
        unset($form['_token']);
        unset($form['password_confirmation']);

        // timestamp 업데이트
        $form['updated_at'] = now();

        // 성공: 배열 반환
        return $form;
    }

    /**
     * 데이터 업데이트 후 호출됩니다.
     */
    public function hookUpdated($wire, $form)
    {
        // 로그인 히스토리에 정보 변경 기록
        if (isset($form['id'])) {
            DB::table('account_logs')->insert([
                'account_id' => $form['id'],
                'action' => 'profile_updated',
                'description' => '관리자에 의한 회원 정보 수정',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
            ]);
        }

        return false;
    }
}