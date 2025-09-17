<?php

namespace Jiny\Auth\App\Http\Controllers\Admin\AuthAccounts;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Admin\App\Services\JsonConfigService;

/**
 * 회원 관리 메인 컨트롤러 (목록/인덱스 페이지)
 *
 * 시스템 회원 계정 목록을 표시하고 관리하는 기능을 제공합니다.
 * Livewire 컴포넌트(AdminTable)와 Hook 패턴을 통해 동작합니다.
 *
 * @package Jiny\Auth\App\Http\Controllers\Admin\AuthAccounts
 * @author  @jiny/auth Team
 * @since   1.0.0
 * 
 * ## 관련 컨트롤러
 * - AuthAccountsCreate: 회원 생성 처리
 * - AuthAccountsEdit: 회원 수정 처리
 * - AuthAccountsDelete: 회원 삭제 처리
 * - AuthAccountsShow: 회원 상세 정보 표시
 * 
 * ## Hook 메소드 호출 트리
 * ```
 * Livewire\AdminTable Component
 * ├── hookIndexing()           [데이터 조회 전]
 * ├── DB Query 실행
 * ├── hookIndexed($rows)       [데이터 조회 후]
 * │   └── grades 테이블 조인
 * ├── hookTableHeader()        [테이블 헤더 설정]
 * ├── hookPagination()         [페이지네이션 설정]
 * ├── hookSorting()           [정렬 설정]
 * ├── hookSearch()            [검색 설정]
 * └── hookFilters()           [필터 설정]
 * ```
 */
class AuthAccounts extends Controller
{
    /**
     * JSON 설정 데이터
     *
     * @var array|null
     */
    private $jsonData;

    /**
     * 컨트롤러 생성자
     *
     * AuthAccounts.json 설정 파일을 로드하여 컨트롤러를 초기화합니다.
     */
    public function __construct()
    {
        // 서비스를 사용하여 JSON 파일 로드
        $jsonConfigService = new JsonConfigService;
        $this->jsonData = $jsonConfigService->loadFromControllerPath(__DIR__);
    }

    /**
     * 회원 목록 페이지 표시
     *
     * 등록된 회원 목록을 테이블 형태로 표시합니다.
     * JSON 설정에 지정된 뷰 템플릿을 사용합니다.
     *
     * @param  Request  $request  HTTP 요청 객체
     * @return \Illuminate\View\View|\Illuminate\Http\Response 회원 목록 뷰 또는 에러 응답
     */
    public function __invoke(Request $request)
    {
        // JSON 데이터 확인
        if (! $this->jsonData) {
            return response('Error: JSON configuration file not found or invalid.', 500);
        }

        // template.index view 경로 확인
        if (! isset($this->jsonData['template']['index'])) {
            return response('Error: 화면을 출력하기 위한 template.index 설정이 필요합니다.', 500);
        }

        // route 정보를 jsonData에 추가
        if (isset($this->jsonData['route']['name'])) {
            $this->jsonData['currentRoute'] = $this->jsonData['route']['name'];
        } elseif (isset($this->jsonData['route']) && is_string($this->jsonData['route'])) {
            // 이전 버전 호환성
            $this->jsonData['currentRoute'] = $this->jsonData['route'];
        }

        // JSON 파일 경로 추가
        $jsonPath = __DIR__.DIRECTORY_SEPARATOR.'AuthAccounts.json';
        $settingsPath = $jsonPath; // settings drawer를 위한 경로

        // 컨트롤러 클래스를 JSON 데이터에 추가
        $this->jsonData['controllerClass'] = get_class($this);

        return view($this->jsonData['template']['index'], [
            'jsonData' => $this->jsonData,
            'jsonPath' => $jsonPath,
            'settingsPath' => $settingsPath,
            'controllerClass' => static::class,
        ]);
    }

    /**
     * Hook: 데이터 조회 전 실행
     *
     * Livewire 컴포넌트가 회원 데이터를 조회하기 전에 호출됩니다.
     * 쿼리 조건을 수정하거나 필터를 추가할 수 있습니다.
     *
     * @param  mixed  $wire  Livewire 컴포넌트 인스턴스
     * @return false|mixed false 반환시 정상 진행, 다른 값 반환시 해당 값이 출력됨
     */
    public function hookIndexing($wire)
    {
        return false;
    }

    /**
     * Hook: 데이터 조회 후 실행
     *
     * 조회된 회원 데이터에 추가 정보를 부가합니다.
     * 회원 등급과 국가 정보를 추가합니다.
     *
     * @param  mixed  $wire  Livewire 컴포넌트 인스턴스
     * @param  mixed  $rows  조회된 데이터
     * @return mixed 가공된 데이터
     */
    public function hookIndexed($wire, $rows)
    {
        // grades 테이블에서 등급 정보 가져오기
        if ($rows && count($rows) > 0) {
            $grades = DB::table('grades')
                ->select('id', 'name')
                ->get()
                ->keyBy('id');
                
            $countries = DB::table('countries')
                ->select('id', 'name')
                ->get()
                ->keyBy('id');

            // 각 회원에 등급 및 국가 이름 추가
            foreach ($rows as $row) {
                if ($row->grade_id && isset($grades[$row->grade_id])) {
                    $row->grade_name = $grades[$row->grade_id]->name;
                } else {
                    $row->grade_name = null;
                }
                
                if ($row->country_id && isset($countries[$row->country_id])) {
                    $row->country_name = $countries[$row->country_id]->name;
                } else {
                    $row->country_name = null;
                }
            }
        }

        return $rows;
    }

    /**
     * Hook: 테이블 헤더 커스터마이징
     *
     * 회원 목록 테이블의 컬럼 헤더를 설정합니다.
     * JSON 설정의 index.table.columns 값을 반환합니다.
     *
     * @param  mixed  $wire  Livewire 컴포넌트 인스턴스
     * @return array 커스터마이징된 헤더 설정
     */
    public function hookTableHeader($wire)
    {
        return $this->jsonData['index']['table']['columns'] ?? [];
    }

    /**
     * Hook: 페이지네이션 설정
     *
     * 한 페이지에 표시할 회원 수와 옵션을 설정합니다.
     * JSON 설정의 index.pagination 값을 반환합니다.
     *
     * @param  mixed  $wire  Livewire 컴포넌트 인스턴스
     * @return array 페이지네이션 설정
     */
    public function hookPagination($wire)
    {
        return $this->jsonData['index']['pagination'] ?? [
            'perPage' => 20,
            'perPageOptions' => [10, 20, 50, 100],
        ];
    }

    /**
     * Hook: 정렬 설정
     *
     * 기본 정렬 컬럼과 방향을 설정합니다.
     * 기본값은 created_at 컬럼의 내림차순입니다.
     *
     * @param  mixed  $wire  Livewire 컴포넌트 인스턴스
     * @return array 정렬 설정
     */
    public function hookSorting($wire)
    {
        return $this->jsonData['index']['sorting'] ?? [
            'default' => 'created_at',
            'direction' => 'desc',
        ];
    }

    /**
     * Hook: 검색 설정
     *
     * 회원 검색 필드의 placeholder와 디바운스 시간을 설정합니다.
     * JSON 설정의 index.search 값을 반환합니다.
     *
     * @param  mixed  $wire  Livewire 컴포넌트 인스턴스
     * @return array 검색 설정
     */
    public function hookSearch($wire)
    {
        return $this->jsonData['index']['search'] ?? [
            'placeholder' => '회원 검색...',
            'debounce' => 300,
        ];
    }

    /**
     * Hook: 필터 설정
     *
     * 회원 목록에 적용할 필터 옵션을 설정합니다.
     * JSON 설정의 index.filters 값을 반환합니다.
     *
     * @param  mixed  $wire  Livewire 컴포넌트 인스턴스
     * @return array 필터 설정
     */
    public function hookFilters($wire)
    {
        return $this->jsonData['index']['filters'] ?? [];
    }
}