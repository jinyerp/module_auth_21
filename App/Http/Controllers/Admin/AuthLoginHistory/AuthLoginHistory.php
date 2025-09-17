<?php

namespace Jiny\Auth\App\Http\Controllers\Admin\AuthLoginHistory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * 로그인 기록 관리 컨트롤러
 * 읽기 전용 로그 관리
 */
class AuthLoginHistory extends Controller
{
    use \Jiny\Admin\Http\Trait\Hook;
    use \Jiny\Admin\Http\Trait\Permit;
    use \Jiny\Admin\Http\Trait\CheckDelete;

    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);

        // 레이아웃 설정
        $this->actions['view']['layout'] = 'jiny-admin::layouts.admin';
        $this->actions['view']['prefix'] = 'jiny-auth::admin.auth_login_history';
        
        // 타이틀 설정
        $this->actions['title'] = "로그인 기록";
        $this->actions['subtitle'] = "사용자 로그인 이력을 관리합니다";
        
        // 읽기 전용 설정
        $this->actions['readonly'] = true;
        
        // Hook 클래스 설정
        $this->actions['controller']['class'] = static::class;
    }

    /**
     * 목록 페이지
     */
    public function index(Request $request)
    {
        // 파라미터 처리
        if ($request->has('account_id')) {
            $this->actions['filter']['account_id'] = $request->get('account_id');
        }
        
        if ($request->has('status')) {
            $this->actions['filter']['status'] = $request->get('status');
        }
        
        if ($request->has('date_from')) {
            $this->actions['filter']['date_from'] = $request->get('date_from');
        }
        
        if ($request->has('date_to')) {
            $this->actions['filter']['date_to'] = $request->get('date_to');
        }

        return view('jiny-auth::admin.auth_login_history.table', [
            'actions' => $this->actions
        ]);
    }

    /**
     * 목록 조회 전 Hook
     */
    public function hookIndexing($wire, $query)
    {
        // 특정 사용자 필터
        if (isset($this->actions['filter']['account_id'])) {
            $query->where('account_id', $this->actions['filter']['account_id']);
        }
        
        // 상태별 필터
        if (isset($this->actions['filter']['status'])) {
            $query->where('status', $this->actions['filter']['status']);
        }
        
        // 날짜 범위 필터
        if (isset($this->actions['filter']['date_from'])) {
            $query->where('login_at', '>=', $this->actions['filter']['date_from'] . ' 00:00:00');
        }
        
        if (isset($this->actions['filter']['date_to'])) {
            $query->where('login_at', '<=', $this->actions['filter']['date_to'] . ' 23:59:59');
        }
        
        // Account 정보 조인
        $query->select('login_histories.*', 'accounts.name as account_name', 'accounts.email as account_email')
              ->leftJoin('accounts', 'login_histories.account_id', '=', 'accounts.id');
        
        return $query;
    }

    /**
     * 목록 조회 후 Hook
     */
    public function hookIndexed($wire, $rows)
    {
        foreach ($rows as &$row) {
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
            } else if ($row->login_at && !$row->logout_at) {
                $row->session_duration = "활성 세션";
            }
            
            // 비정상 패턴 감지
            $row->is_suspicious = $this->detectSuspiciousLogin($row);
            
            // 디바이스 타입 아이콘
            $row->device_icon = $this->getDeviceIcon($row->device_type);
            
            // 브라우저 아이콘
            $row->browser_icon = $this->getBrowserIcon($row->browser);
            
            // 위치 정보 포맷
            if ($row->location) {
                $location = json_decode($row->location, true);
                if ($location) {
                    $row->location_display = ($location['city'] ?? '') . ', ' . ($location['country'] ?? '');
                }
            }
        }
        
        return $rows;
    }

    /**
     * 비정상 로그인 패턴 감지
     */
    private function detectSuspiciousLogin($row)
    {
        // 실패한 로그인
        if ($row->status === 'failed') {
            return true;
        }
        
        // 새벽 시간대 로그인 (2AM - 5AM)
        $loginHour = Carbon::parse($row->login_at)->hour;
        if ($loginHour >= 2 && $loginHour <= 5) {
            return true;
        }
        
        // 짧은 세션 (1분 미만)
        if ($row->logout_at) {
            $duration = Carbon::parse($row->logout_at)->diffInSeconds(Carbon::parse($row->login_at));
            if ($duration < 60) {
                return true;
            }
        }
        
        // 동시 로그인 체크
        if ($this->hasSimultaneousLogin($row)) {
            return true;
        }
        
        return false;
    }

    /**
     * 동시 로그인 체크
     */
    private function hasSimultaneousLogin($row)
    {
        $overlapping = DB::table('login_histories')
            ->where('account_id', $row->account_id)
            ->where('id', '!=', $row->id)
            ->where('login_at', '<=', $row->logout_at ?? now())
            ->where(function($query) use ($row) {
                $query->whereNull('logout_at')
                      ->orWhere('logout_at', '>=', $row->login_at);
            })
            ->exists();
            
        return $overlapping;
    }

    /**
     * 디바이스 아이콘 반환
     */
    private function getDeviceIcon($deviceType)
    {
        $icons = [
            'desktop' => '🖥️',
            'mobile' => '📱',
            'tablet' => '📋',
            'unknown' => '❓'
        ];
        
        return $icons[$deviceType] ?? $icons['unknown'];
    }

    /**
     * 브라우저 아이콘 반환
     */
    private function getBrowserIcon($browser)
    {
        if (stripos($browser, 'chrome') !== false) return '🌐';
        if (stripos($browser, 'firefox') !== false) return '🦊';
        if (stripos($browser, 'safari') !== false) return '🧭';
        if (stripos($browser, 'edge') !== false) return '🌊';
        if (stripos($browser, 'opera') !== false) return '🎭';
        return '🌍';
    }

    /**
     * 통계 정보 생성
     */
    public function hookCustomStats($wire)
    {
        $stats = [];
        
        // 오늘 로그인 수
        $stats['today'] = DB::table('login_histories')
            ->whereDate('login_at', today())
            ->count();
        
        // 활성 세션 수
        $stats['active'] = DB::table('login_histories')
            ->whereNull('logout_at')
            ->where('login_at', '>=', now()->subHours(24))
            ->count();
        
        // 실패 로그인 수 (최근 24시간)
        $stats['failed'] = DB::table('login_histories')
            ->where('status', 'failed')
            ->where('login_at', '>=', now()->subHours(24))
            ->count();
        
        // 의심스러운 활동 수
        $stats['suspicious'] = DB::table('login_histories')
            ->where('login_at', '>=', now()->subHours(24))
            ->where(function($query) {
                $query->where('status', 'failed')
                      ->orWhereRaw('HOUR(login_at) BETWEEN 2 AND 5');
            })
            ->count();
        
        return $stats;
    }
}