<?php

namespace Jiny\Auth\App\Http\Controllers\Admin\AuthAccountLogs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Admin\App\Services\JsonConfigService;
use Jiny\Auth\App\Models\AccountLog;

/**
 * AuthAccountLogsShow Controller
 *
 * 회원 활동 로그 상세 보기
 */
class AuthAccountLogsShow extends Controller
{
    private $jsonData;

    public function __construct()
    {
        // 서비스를 사용하여 JSON 파일 로드
        $jsonConfigService = new JsonConfigService;
        $this->jsonData = $jsonConfigService->loadFromControllerPath(
            dirname(__DIR__).DIRECTORY_SEPARATOR.'AuthAccountLogs'
        );
    }

    /**
     * Display the specified resource.
     */
    public function __invoke(Request $request, $id)
    {
        if (! $this->jsonData) {
            return response('Error: JSON 데이터를 로드할 수 없습니다.', 500);
        }

        // 데이터 조회
        $model = $this->jsonData['table']['model'] ?? AccountLog::class;
        $data = $model::findOrFail($id);

        // JSON 파일 경로 추가
        $jsonPath = dirname(__DIR__).DIRECTORY_SEPARATOR.'AuthAccountLogs'.DIRECTORY_SEPARATOR.'AuthAccountLogs.json';
        $settingsPath = $jsonPath;

        // currentRoute 설정
        $this->jsonData['currentRoute'] = 'admin.auth.account.logs.show';

        // 컨트롤러 클래스를 JSON 데이터에 추가
        $this->jsonData['controllerClass'] = get_class($this);

        return view($this->jsonData['template']['show'], [
            'jsonData' => $this->jsonData,
            'jsonPath' => $jsonPath,
            'settingsPath' => $settingsPath,
            'controllerClass' => static::class,
            'data' => $data,
            'id' => $id,
            'title' => '로그 상세 정보',
            'subtitle' => '로그 ID: ' . $id,
        ]);
    }

    /**
     * Hook: 상세 데이터 표시 전 처리
     */
    public function hookShowing($wire, $id)
    {
        // 로그 데이터 조회 시 관련 정보 로드
        $log = AccountLog::with('account')->find($id);
        
        if ($log && $log->ip_address) {
            // IP 기반 추가 정보 설정
            $log->location = $this->getLocationFromIP($log->ip_address);
            
            // User Agent 파싱
            if ($log->user_agent) {
                $log->browser_info = $this->parseBrowserInfo($log->user_agent);
            }
            
            // 의심스러운 활동 체크
            $log->is_suspicious = $this->checkSuspiciousActivity($log);
            
            // 동일 IP에서의 최근 활동
            $log->recent_activities_from_ip = AccountLog::where('ip_address', $log->ip_address)
                ->where('id', '!=', $id)
                ->orderBy('performed_at', 'desc')
                ->limit(5)
                ->get();
        }
    }

    /**
     * Hook: 상세 데이터 표시 후 처리
     */
    public function hookShowed($wire, $record)
    {
        // 추가 정보 설정
        if ($record) {
            // JSON 데이터 포맷팅
            if ($record->old_values) {
                $record->old_values_formatted = json_encode($record->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            }
            
            if ($record->new_values) {
                $record->new_values_formatted = json_encode($record->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            }
            
            if ($record->meta) {
                $record->meta_formatted = json_encode($record->meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            }
        }
        
        return $record;
    }

    /**
     * IP 주소로부터 지역 정보를 가져옴
     */
    private function getLocationFromIP($ip)
    {
        // 로컬 IP 체크
        if (in_array($ip, ['127.0.0.1', '::1'])) {
            return 'Localhost';
        }
        
        // 실제로는 GeoIP2 등의 서비스를 사용
        return 'Unknown Location';
    }

    /**
     * User Agent에서 브라우저 정보 파싱
     */
    private function parseBrowserInfo($userAgent)
    {
        $info = [];
        
        // 간단한 브라우저 감지
        if (strpos($userAgent, 'Chrome') !== false) {
            $info['browser'] = 'Chrome';
        } elseif (strpos($userAgent, 'Firefox') !== false) {
            $info['browser'] = 'Firefox';
        } elseif (strpos($userAgent, 'Safari') !== false) {
            $info['browser'] = 'Safari';
        } else {
            $info['browser'] = 'Unknown';
        }
        
        // OS 감지
        if (strpos($userAgent, 'Windows') !== false) {
            $info['os'] = 'Windows';
        } elseif (strpos($userAgent, 'Mac') !== false) {
            $info['os'] = 'macOS';
        } elseif (strpos($userAgent, 'Linux') !== false) {
            $info['os'] = 'Linux';
        } elseif (strpos($userAgent, 'Android') !== false) {
            $info['os'] = 'Android';
        } elseif (strpos($userAgent, 'iOS') !== false || strpos($userAgent, 'iPhone') !== false) {
            $info['os'] = 'iOS';
        } else {
            $info['os'] = 'Unknown';
        }
        
        return $info;
    }

    /**
     * 의심스러운 활동 체크
     */
    private function checkSuspiciousActivity($row)
    {
        // 실패한 작업
        if ($row->status == 'failed') {
            return true;
        }
        
        // 비정상적인 시간대
        $hour = $row->performed_at->hour;
        if ($hour >= 2 && $hour <= 5) {
            return true;
        }
        
        // 민감한 작업
        $sensitiveActions = ['password_reset', 'email_change', 'account_delete', 'permission_change'];
        if (in_array($row->action, $sensitiveActions)) {
            return true;
        }
        
        return false;
    }
}