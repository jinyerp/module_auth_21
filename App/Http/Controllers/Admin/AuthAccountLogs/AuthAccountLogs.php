<?php

namespace Jiny\Auth\App\Http\Controllers\Admin\AuthAccountLogs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Admin\App\Services\JsonConfigService;
use Jiny\Auth\App\Models\AccountLog;

/**
 * AuthAccountLogs Main Controller
 *
 * 회원 활동 로그 조회
 * 읽기 전용 로그이므로 생성 기능 제외
 */
class AuthAccountLogs extends Controller
{
    private $jsonData;

    public function __construct()
    {
        // 서비스를 사용하여 JSON 파일 로드
        $jsonConfigService = new JsonConfigService;
        $this->jsonData = $jsonConfigService->loadFromControllerPath(__DIR__);
    }

    /**
     * Display a listing of the resource.
     */
    public function __invoke(Request $request)
    {
        if (! $this->jsonData) {
            return response('Error: JSON 데이터를 로드할 수 없습니다.', 500);
        }

        // JSON 파일 경로 추가
        $jsonPath = __DIR__.DIRECTORY_SEPARATOR.'AuthAccountLogs.json';
        $settingsPath = $jsonPath;

        // currentRoute 설정
        $this->jsonData['currentRoute'] = 'admin.auth.account.logs';

        // 컨트롤러 클래스를 JSON 데이터에 추가
        $this->jsonData['controllerClass'] = get_class($this);

        // 쿼리 스트링 파라미터를 jsonData에 동적으로 추가
        $queryParams = $request->query();
        if (! empty($queryParams)) {
            // 동적 쿼리 조건을 위한 키 추가
            $this->jsonData['queryConditions'] = [];

            // account_id 파라미터 처리
            if (isset($queryParams['account_id'])) {
                $this->jsonData['queryConditions']['account_id'] = $queryParams['account_id'];
                // 필터에도 추가 (UI 표시용)
                $this->jsonData['index']['filters']['account_id']['value'] = $queryParams['account_id'];
                $this->jsonData['index']['defaultFilters'] = ['account_id' => $queryParams['account_id']];
            }

            // 다른 쿼리 파라미터들도 처리 가능
            foreach (['action', 'ip_address', 'status', 'date_from', 'date_to'] as $param) {
                if (isset($queryParams[$param])) {
                    $this->jsonData['queryConditions'][$param] = $queryParams[$param];
                }
            }
        }

        return view($this->jsonData['template']['index'], [
            'jsonData' => $this->jsonData,
            'jsonPath' => $jsonPath,
            'settingsPath' => $settingsPath,
            'controllerClass' => static::class,
            'title' => $this->jsonData['title'] ?? '회원 활동 로그',
            'subtitle' => $this->jsonData['subtitle'] ?? '회원 활동 이력 조회',
        ]);
    }

    /**
     * Hook: 인덱스 데이터 조회 전 처리
     * 특정 회원의 로그만 필터링하고 의심스러운 활동 표시
     */
    public function hookIndexing($wire)
    {
        // 쿼리 조건이 있으면 적용
        if (isset($this->jsonData['queryConditions'])) {
            $query = $wire->query;
            
            if (isset($this->jsonData['queryConditions']['account_id'])) {
                $query->where('account_id', $this->jsonData['queryConditions']['account_id']);
            }
            
            if (isset($this->jsonData['queryConditions']['action'])) {
                $query->where('action', $this->jsonData['queryConditions']['action']);
            }
            
            if (isset($this->jsonData['queryConditions']['status'])) {
                $query->where('status', $this->jsonData['queryConditions']['status']);
            }
            
            if (isset($this->jsonData['queryConditions']['ip_address'])) {
                $query->where('ip_address', 'like', '%' . $this->jsonData['queryConditions']['ip_address'] . '%');
            }
            
            if (isset($this->jsonData['queryConditions']['date_from'])) {
                $query->where('performed_at', '>=', $this->jsonData['queryConditions']['date_from']);
            }
            
            if (isset($this->jsonData['queryConditions']['date_to'])) {
                $query->where('performed_at', '<=', $this->jsonData['queryConditions']['date_to']);
            }
        }
    }

    /**
     * Hook: 인덱스 데이터 조회 후 처리
     * IP 기반 지역 정보 표시 및 의심스러운 활동 하이라이트
     */
    public function hookIndexed($wire, $rows)
    {
        // 각 행에 추가 정보 처리
        foreach ($rows as $row) {
            // IP 주소 기반 지역 정보 추가 (간단한 예시)
            if ($row->ip_address) {
                $row->location = $this->getLocationFromIP($row->ip_address);
            }
            
            // 의심스러운 활동 플래그
            $row->is_suspicious = $this->checkSuspiciousActivity($row);
        }
        
        return $rows;
    }

    /**
     * IP 주소로부터 지역 정보를 가져옴
     * 실제 구현시 GeoIP 서비스 활용 가능
     */
    private function getLocationFromIP($ip)
    {
        // 로컬 IP 체크
        if (in_array($ip, ['127.0.0.1', '::1'])) {
            return 'Local';
        }
        
        // 여기서는 간단한 예시만 제공
        // 실제로는 GeoIP2 등의 서비스를 사용하여 구현
        return 'Unknown';
    }

    /**
     * 의심스러운 활동 체크
     */
    private function checkSuspiciousActivity($row)
    {
        // 실패한 로그인 시도
        if ($row->action == 'login_failed' && $row->status == 'failed') {
            return true;
        }
        
        // 짧은 시간내 여러 번 실패한 경우
        if ($row->action == 'login_failed') {
            $recentFailures = AccountLog::where('account_id', $row->account_id)
                ->where('action', 'login_failed')
                ->where('performed_at', '>=', now()->subMinutes(10))
                ->count();
                
            if ($recentFailures >= 3) {
                return true;
            }
        }
        
        // 비정상적인 시간대 접근 (예: 새벽 2-5시)
        $hour = $row->performed_at->hour;
        if ($hour >= 2 && $hour <= 5) {
            return true;
        }
        
        return false;
    }
}