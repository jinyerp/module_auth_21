<?php

namespace Jiny\Auth\App\Http\Controllers\Admin\AuthLoginHistory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * 로그인 기록 상세보기 컨트롤러
 */
class AuthLoginHistoryShow extends Controller
{
    use \Jiny\Admin\Http\Trait\Hook;
    use \Jiny\Admin\Http\Trait\Permit;

    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);

        // 레이아웃 설정
        $this->actions['view']['layout'] = 'jiny-admin::layouts.admin';
        $this->actions['view']['prefix'] = 'jiny-auth::admin.auth_login_history';
        
        // 타이틀 설정
        $this->actions['title'] = "로그인 기록 상세";
        $this->actions['subtitle'] = "로그인 세션 상세 정보";
        
        // 읽기 전용 설정
        $this->actions['readonly'] = true;
        
        // Hook 클래스 설정
        $this->actions['controller']['class'] = static::class;
    }

    /**
     * 상세 페이지
     */
    public function index(Request $request, $id)
    {
        $this->actions['id'] = $id;
        
        return view('jiny-auth::admin.auth_login_history.show', [
            'actions' => $this->actions
        ]);
    }

    /**
     * 상세 조회 전 Hook
     */
    public function hookShowing($wire, $id)
    {
        // 관련 데이터 조인
        $query = DB::table('login_histories')
            ->select('login_histories.*', 
                     'accounts.name as account_name',
                     'accounts.email as account_email',
                     'accounts.phone as account_phone',
                     'accounts.avatar as account_avatar')
            ->leftJoin('accounts', 'login_histories.account_id', '=', 'accounts.id')
            ->where('login_histories.id', $id);
            
        return $query;
    }

    /**
     * 상세 조회 후 Hook
     */
    public function hookShowed($wire, $row)
    {
        if (!$row) return $row;
        
        // 세션 지속 시간 계산
        if ($row->login_at && $row->logout_at) {
            $login = Carbon::parse($row->login_at);
            $logout = Carbon::parse($row->logout_at);
            $duration = $logout->diffInMinutes($login);
            
            if ($duration >= 60) {
                $hours = floor($duration / 60);
                $minutes = $duration % 60;
                $row->session_duration = "{$hours}시간 {$minutes}분";
            } else {
                $row->session_duration = "{$duration}분";
            }
            
            // 상세 시간 정보
            $row->duration_seconds = $logout->diffInSeconds($login);
            $row->duration_formatted = $logout->diffForHumans($login, true);
        } else if ($row->login_at && !$row->logout_at) {
            $row->session_duration = "활성 세션";
            $login = Carbon::parse($row->login_at);
            $row->duration_formatted = $login->diffForHumans();
        }
        
        // 위치 정보 파싱
        if ($row->location) {
            $location = json_decode($row->location, true);
            if ($location) {
                $row->location_data = $location;
                $row->location_display = $this->formatLocation($location);
            }
        }
        
        // User Agent 파싱
        if ($row->user_agent) {
            $row->parsed_agent = $this->parseUserAgent($row->user_agent);
        }
        
        // 동일 IP 최근 활동
        $row->ip_activities = $this->getRecentIpActivities($row->ip_address, $row->account_id);
        
        // 동일 계정 최근 활동
        $row->account_activities = $this->getRecentAccountActivities($row->account_id, $row->id);
        
        // 보안 분석
        $row->security_analysis = $this->analyzeSecurityRisk($row);
        
        return $row;
    }

    /**
     * 위치 정보 포맷
     */
    private function formatLocation($location)
    {
        $parts = [];
        
        if (!empty($location['city'])) $parts[] = $location['city'];
        if (!empty($location['region'])) $parts[] = $location['region'];
        if (!empty($location['country'])) $parts[] = $location['country'];
        
        return implode(', ', $parts) ?: '알 수 없는 위치';
    }

    /**
     * User Agent 파싱
     */
    private function parseUserAgent($userAgent)
    {
        $result = [
            'browser' => 'Unknown',
            'browser_version' => '',
            'os' => 'Unknown',
            'os_version' => '',
            'device' => 'Unknown'
        ];
        
        // 브라우저 감지
        if (preg_match('/Chrome\/([0-9.]+)/', $userAgent, $matches)) {
            $result['browser'] = 'Chrome';
            $result['browser_version'] = $matches[1];
        } elseif (preg_match('/Firefox\/([0-9.]+)/', $userAgent, $matches)) {
            $result['browser'] = 'Firefox';
            $result['browser_version'] = $matches[1];
        } elseif (preg_match('/Safari\/([0-9.]+)/', $userAgent, $matches)) {
            $result['browser'] = 'Safari';
            $result['browser_version'] = $matches[1];
        } elseif (preg_match('/Edge\/([0-9.]+)/', $userAgent, $matches)) {
            $result['browser'] = 'Edge';
            $result['browser_version'] = $matches[1];
        }
        
        // OS 감지
        if (preg_match('/Windows NT ([0-9.]+)/', $userAgent, $matches)) {
            $result['os'] = 'Windows';
            $result['os_version'] = $this->getWindowsVersion($matches[1]);
        } elseif (preg_match('/Mac OS X ([0-9_]+)/', $userAgent, $matches)) {
            $result['os'] = 'macOS';
            $result['os_version'] = str_replace('_', '.', $matches[1]);
        } elseif (preg_match('/Android ([0-9.]+)/', $userAgent, $matches)) {
            $result['os'] = 'Android';
            $result['os_version'] = $matches[1];
        } elseif (preg_match('/iPhone OS ([0-9_]+)/', $userAgent, $matches)) {
            $result['os'] = 'iOS';
            $result['os_version'] = str_replace('_', '.', $matches[1]);
        }
        
        // 디바이스 타입 감지
        if (preg_match('/Mobile|Android|iPhone/', $userAgent)) {
            $result['device'] = 'Mobile';
        } elseif (preg_match('/Tablet|iPad/', $userAgent)) {
            $result['device'] = 'Tablet';
        } else {
            $result['device'] = 'Desktop';
        }
        
        return $result;
    }

    /**
     * Windows 버전 변환
     */
    private function getWindowsVersion($nt)
    {
        $versions = [
            '10.0' => '10/11',
            '6.3' => '8.1',
            '6.2' => '8',
            '6.1' => '7',
            '6.0' => 'Vista',
            '5.1' => 'XP'
        ];
        
        return $versions[$nt] ?? $nt;
    }

    /**
     * 동일 IP 최근 활동 조회
     */
    private function getRecentIpActivities($ip, $excludeAccountId)
    {
        return DB::table('login_histories')
            ->select('login_histories.*', 'accounts.name', 'accounts.email')
            ->leftJoin('accounts', 'login_histories.account_id', '=', 'accounts.id')
            ->where('ip_address', $ip)
            ->where('account_id', '!=', $excludeAccountId)
            ->orderBy('login_at', 'desc')
            ->limit(5)
            ->get();
    }

    /**
     * 동일 계정 최근 활동 조회
     */
    private function getRecentAccountActivities($accountId, $excludeId)
    {
        return DB::table('login_histories')
            ->where('account_id', $accountId)
            ->where('id', '!=', $excludeId)
            ->orderBy('login_at', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * 보안 위험 분석
     */
    private function analyzeSecurityRisk($row)
    {
        $risks = [];
        
        // 실패한 로그인
        if ($row->status === 'failed') {
            $risks[] = [
                'level' => 'warning',
                'message' => '로그인 실패'
            ];
        }
        
        // 새벽 시간대 로그인
        $loginHour = Carbon::parse($row->login_at)->hour;
        if ($loginHour >= 2 && $loginHour <= 5) {
            $risks[] = [
                'level' => 'info',
                'message' => '비정상 시간대 접속 (새벽 ' . $loginHour . '시)'
            ];
        }
        
        // 짧은 세션
        if ($row->logout_at) {
            $duration = Carbon::parse($row->logout_at)->diffInSeconds(Carbon::parse($row->login_at));
            if ($duration < 60) {
                $risks[] = [
                    'level' => 'warning',
                    'message' => '매우 짧은 세션 (' . $duration . '초)'
                ];
            }
        }
        
        // 다중 IP 사용
        $multiIp = DB::table('login_histories')
            ->where('account_id', $row->account_id)
            ->where('login_at', '>=', Carbon::parse($row->login_at)->subHour())
            ->where('login_at', '<=', Carbon::parse($row->login_at)->addHour())
            ->distinct('ip_address')
            ->count('ip_address');
            
        if ($multiIp > 2) {
            $risks[] = [
                'level' => 'danger',
                'message' => '짧은 시간 내 여러 IP 사용 (' . $multiIp . '개)'
            ];
        }
        
        return $risks;
    }
}