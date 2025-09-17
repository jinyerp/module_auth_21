<?php

namespace Jiny\Auth\App\Http\Controllers\Admin\AuthAccounts;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Jiny\Admin\App\Services\JsonConfigService;
use Jiny\Admin\App\Services\PasswordValidator;

/**
 * 회원 생성 컨트롤러
 * 
 * 새로운 회원을 생성하는 폼 표시 및 처리를 담당합니다.
 * Livewire 컴포넌트(AdminCreate)와 Hook 패턴을 통해 동작합니다.
 * 
 * @package Jiny\Auth\App\Http\Controllers\Admin\AuthAccounts
 * @author  @jiny/auth Team
 * @since   1.0.0
 * 
 * ## Hook 메소드 호출 트리
 * ```
 * Livewire\AdminCreate Component
 * ├── hookCreating($value)                [폼 초기화]
 * │   ├── grades 테이블 조회
 * │   ├── countries 테이블 조회
 * │   └── 기본값 설정
 * ├── hookFormEmail($value)               [이메일 실시간 검증]
 * │   ├── 이메일 형식 검증
 * │   └── 중복 체크
 * ├── hookFormPassword($value)            [패스워드 실시간 검증]
 * │   └── PasswordValidator::validate()
 * ├── hookFormPasswordConfirmation($value)[패스워드 확인 검증]
 * │   └── 일치 여부 체크
 * ├── hookStoring($form)                  [저장 전 처리]
 * │   ├── 패스워드 확인 검증
 * │   ├── PasswordValidator::validate()
 * │   ├── Hash::make()
 * │   └── 타임스탬프 추가
 * └── hookStored($form)                   [저장 후 처리]
 * ```
 * 
 * ## 반환값 패턴
 * - hookStoring: 
 *   - 성공: array (처리된 폼 데이터)
 *   - 실패: string (에러 메시지)
 * - hookForm*: void (에러는 Livewire에 직접 전달)
 */
class AuthAccountsCreate extends Controller
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
     * 생성 폼 표시
     */
    public function __invoke(Request $request)
    {
        // JSON 데이터 확인
        if (! $this->jsonData) {
            return response('Error: JSON 데이터를 로드할 수 없습니다.', 500);
        }

        // 기본값 설정
        $form = [];

        // route 정보를 jsonData에 추가
        if (isset($this->jsonData['route']['name'])) {
            $this->jsonData['currentRoute'] = $this->jsonData['route']['name'];
        } elseif (isset($this->jsonData['route']) && is_string($this->jsonData['route'])) {
            // 이전 버전 호환성
            $this->jsonData['currentRoute'] = $this->jsonData['route'];
        }

        // template.create view 경로 확인
        if (! isset($this->jsonData['template']['create'])) {
            $debugInfo = 'JSON template section: '.json_encode($this->jsonData['template'] ?? 'not found');

            return response('Error: 화면을 출력하기 위한 template.create 설정이 필요합니다. '.$debugInfo, 500);
        }

        // JSON 파일 경로 추가
        $jsonPath = __DIR__.DIRECTORY_SEPARATOR.'AuthAccounts.json';
        $settingsPath = $jsonPath; // settings drawer를 위한 경로

        // 현재 컨트롤러 클래스를 JSON 데이터에 추가
        $this->jsonData['controllerClass'] = get_class($this);

        return view($this->jsonData['template']['create'], [
            'jsonData' => $this->jsonData,
            'jsonPath' => $jsonPath,
            'settingsPath' => $settingsPath,
            'form' => $form,
        ]);
    }

    /**
     * 생성폼이 실행될때 호출됩니다.
     */
    public function hookCreating($wire, $value)
    {
        // 기본값 설정
        $defaults = $this->jsonData['create']['defaults'] ??
                   $this->jsonData['store']['defaults'] ?? [];

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

        // 폼 기본값 설정
        $form = array_merge([
            'status' => 'active',      // 기본값: 활성
            'grade_id' => null,         // 기본값: 선택 안함
            'country_id' => null,       // 기본값: 선택 안함
        ], $defaults, $value);

        return $form;
    }

    /**
     * 이메일 필드 실시간 검증
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

        // 이메일 중복 체크
        $exists = DB::table('accounts')
            ->where('email', $value)
            ->exists();

        if ($exists) {
            $wire->addError('form.email', '이미 등록된 이메일 주소입니다.');
        }
    }

    /**
     * 패스워드 필드 실시간 검증
     */
    public function hookFormPassword($wire, $value, $fieldName = null)
    {
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
     * 신규 데이터 DB 삽입전에 호출됩니다.
     *
     * @return array|string 성공시 수정된 form 배열, 실패시 에러 메시지 문자열
     */
    public function hookStoring($wire, $form)
    {
        // 패스워드 확인 필드 검증
        if (isset($form['password']) && isset($form['password_confirmation'])) {
            if ($form['password'] !== $form['password_confirmation']) {
                $errorMessage = '패스워드와 패스워드 확인이 일치하지 않습니다.';

                // Livewire 컴포넌트에 에러 전달
                if ($wire && method_exists($wire, 'addError')) {
                    $wire->addError('form.password_confirmation', $errorMessage);
                }

                return $errorMessage;
            }
        }

        // 패스워드 검증
        if (isset($form['password'])) {
            $passwordValidator = new PasswordValidator;

            // 사용자 정보 준비 (유사성 체크용)
            $userData = [
                'name' => $form['name'] ?? '',
                'email' => $form['email'] ?? '',
            ];

            // 패스워드 유효성 검증
            if (!$passwordValidator->validate($form['password'], $userData)) {
                // 검증 실패 시 에러 메시지 문자열 반환
                $errors = $passwordValidator->getErrors();
                $errorMessage = '패스워드 검증 실패: '.implode(' ', $errors);

                // Livewire 컴포넌트에 에러 전달
                if ($wire && method_exists($wire, 'addError')) {
                    foreach ($errors as $error) {
                        $wire->addError('form.password', $error);
                    }
                }

                // 에러 메시지 문자열 반환 (배열이 아님)
                return $errorMessage;
            }

            // 검증 통과 시 패스워드 해싱
            $form['password'] = Hash::make($form['password']);
        }

        // 불필요한 필드 제거
        unset($form['_token']);
        unset($form['continue_creating']);
        unset($form['password_confirmation']);

        // timestamps 추가
        $form['created_at'] = now();
        $form['updated_at'] = now();

        // 성공: 배열 반환
        return $form;
    }

    /**
     * 신규 데이터 DB 삽입 후 호출됩니다.
     */
    public function hookStored($wire, $form)
    {
        // 로그인 히스토리에 회원 가입 기록
        if (isset($form['id'])) {
            DB::table('login_histories')->insert([
                'account_id' => $form['id'],
                'action' => 'register',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
            ]);
        }

        return false;
    }
}