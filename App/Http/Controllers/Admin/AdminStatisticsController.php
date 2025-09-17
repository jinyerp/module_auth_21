<?php

namespace Jiny\Auth\App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Carbon\Carbon;

class AdminStatisticsController extends Controller
{
    /**
     * 가입 통계
     * GET /admin/auth/statistics/registrations
     */
    public function registrations(Request $request)
    {
        $period = $request->get('period', '30'); // 기본 30일
        $startDate = now()->subDays($period);
        
        // 일별 가입자 수
        $dailyRegistrations = User::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        // 월별 가입자 수 (최근 12개월)
        $monthlyRegistrations = User::select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get();
        
        // 가입 경로별 통계
        $registrationSources = DB::table('auth_account_logs')
            ->where('event', 'user_registered')
            ->where('created_at', '>=', $startDate)
            ->select(
                DB::raw('JSON_EXTRACT(metadata, "$.source") as source'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('source')
            ->get();
        
        // 가입 유형별 통계
        $registrationTypes = User::join('auth_user_types', 'users.user_type_id', '=', 'auth_user_types.id')
            ->where('users.created_at', '>=', $startDate)
            ->select(
                'auth_user_types.name as type_name',
                'auth_user_types.code as type_code',
                DB::raw('COUNT(users.id) as count')
            )
            ->groupBy('auth_user_types.id', 'auth_user_types.name', 'auth_user_types.code')
            ->get();
        
        // 시간대별 가입 패턴
        $hourlyPattern = User::select(
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', $startDate)
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();
        
        // 요일별 가입 패턴
        $weekdayPattern = User::select(
                DB::raw('DAYOFWEEK(created_at) as weekday'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', $startDate)
            ->groupBy('weekday')
            ->orderBy('weekday')
            ->get();
        
        // 전체 통계
        $totalStats = [
            'total_users' => User::count(),
            'period_registrations' => User::where('created_at', '>=', $startDate)->count(),
            'today_registrations' => User::whereDate('created_at', today())->count(),
            'yesterday_registrations' => User::whereDate('created_at', today()->subDay())->count(),
            'this_week' => User::where('created_at', '>=', now()->startOfWeek())->count(),
            'this_month' => User::whereMonth('created_at', now()->month)->count(),
            'avg_daily' => round(User::where('created_at', '>=', $startDate)->count() / $period, 1),
        ];
        
        return view('jiny-auth::admin.statistics.registrations', compact(
            'dailyRegistrations',
            'monthlyRegistrations',
            'registrationSources',
            'registrationTypes',
            'hourlyPattern',
            'weekdayPattern',
            'totalStats'
        ));
    }
    
    /**
     * 활성 사용자 통계
     * GET /admin/auth/statistics/active-users
     */
    public function activeUsers(Request $request)
    {
        $period = $request->get('period', '30');
        $startDate = now()->subDays($period);
        
        // DAU (Daily Active Users)
        $dau = DB::table('auth_login_histories')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(DISTINCT user_id) as count')
            )
            ->where('created_at', '>=', $startDate)
            ->where('status', 'success')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        // WAU (Weekly Active Users)
        $wau = DB::table('auth_login_histories')
            ->select(
                DB::raw('YEARWEEK(created_at) as week'),
                DB::raw('COUNT(DISTINCT user_id) as count')
            )
            ->where('created_at', '>=', now()->subWeeks(12))
            ->where('status', 'success')
            ->groupBy('week')
            ->orderBy('week')
            ->get();
        
        // MAU (Monthly Active Users)
        $mau = DB::table('auth_login_histories')
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('COUNT(DISTINCT user_id) as count')
            )
            ->where('created_at', '>=', now()->subMonths(12))
            ->where('status', 'success')
            ->groupBy('month')
            ->orderBy('month')
            ->get();
        
        // 활성도별 사용자 분류
        $activityLevels = [
            'very_active' => DB::table('auth_login_histories')
                ->where('created_at', '>=', $startDate)
                ->where('status', 'success')
                ->groupBy('user_id')
                ->havingRaw('COUNT(*) >= ?', [$period * 0.8])
                ->count(DB::raw('DISTINCT user_id')),
            'active' => DB::table('auth_login_histories')
                ->where('created_at', '>=', $startDate)
                ->where('status', 'success')
                ->groupBy('user_id')
                ->havingRaw('COUNT(*) BETWEEN ? AND ?', [$period * 0.3, $period * 0.8])
                ->count(DB::raw('DISTINCT user_id')),
            'moderate' => DB::table('auth_login_histories')
                ->where('created_at', '>=', $startDate)
                ->where('status', 'success')
                ->groupBy('user_id')
                ->havingRaw('COUNT(*) BETWEEN ? AND ?', [$period * 0.1, $period * 0.3])
                ->count(DB::raw('DISTINCT user_id')),
            'inactive' => User::whereNotIn('id', function($query) use ($startDate) {
                $query->select('user_id')
                    ->from('auth_login_histories')
                    ->where('created_at', '>=', $startDate)
                    ->where('status', 'success');
            })->count(),
        ];
        
        // 디바이스별 활성 사용자
        $deviceActivity = DB::table('auth_user_devices')
            ->select(
                'device_type',
                DB::raw('COUNT(DISTINCT user_id) as users'),
                DB::raw('COUNT(*) as devices')
            )
            ->where('last_active_at', '>=', $startDate)
            ->groupBy('device_type')
            ->get();
        
        // 시간대별 활성도
        $hourlyActivity = DB::table('auth_login_histories')
            ->select(
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('COUNT(*) as logins'),
                DB::raw('COUNT(DISTINCT user_id) as unique_users')
            )
            ->where('created_at', '>=', $startDate)
            ->where('status', 'success')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();
        
        // 현재 활성 세션
        $activeSessions = DB::table('sessions')
            ->where('last_activity', '>=', now()->subMinutes(30)->timestamp)
            ->count();
        
        // 통계 요약
        $summary = [
            'total_users' => User::count(),
            'dau_today' => DB::table('auth_login_histories')
                ->whereDate('created_at', today())
                ->where('status', 'success')
                ->count(DB::raw('DISTINCT user_id')),
            'wau_current' => DB::table('auth_login_histories')
                ->where('created_at', '>=', now()->subWeek())
                ->where('status', 'success')
                ->count(DB::raw('DISTINCT user_id')),
            'mau_current' => DB::table('auth_login_histories')
                ->where('created_at', '>=', now()->subMonth())
                ->where('status', 'success')
                ->count(DB::raw('DISTINCT user_id')),
            'active_sessions' => $activeSessions,
        ];
        
        return view('jiny-auth::admin.statistics.active-users', compact(
            'dau',
            'wau',
            'mau',
            'activityLevels',
            'deviceActivity',
            'hourlyActivity',
            'summary'
        ));
    }
    
    /**
     * 로그인 패턴 분석
     * GET /admin/auth/statistics/login-patterns
     */
    public function loginPatterns(Request $request)
    {
        $period = $request->get('period', '30');
        $startDate = now()->subDays($period);
        
        // 로그인 성공/실패 비율
        $loginResults = DB::table('auth_login_histories')
            ->where('created_at', '>=', $startDate)
            ->select(
                'status',
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('status')
            ->get();
        
        // 인증 방법별 통계
        $authMethods = DB::table('auth_login_histories')
            ->where('created_at', '>=', $startDate)
            ->where('status', 'success')
            ->select(
                'auth_method',
                DB::raw('COUNT(*) as count'),
                DB::raw('COUNT(DISTINCT user_id) as unique_users')
            )
            ->groupBy('auth_method')
            ->get();
        
        // 브라우저별 로그인
        $browserStats = DB::table('auth_login_histories')
            ->where('created_at', '>=', $startDate)
            ->where('status', 'success')
            ->select(
                'browser',
                DB::raw('COUNT(*) as count'),
                DB::raw('COUNT(DISTINCT user_id) as unique_users')
            )
            ->groupBy('browser')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();
        
        // 플랫폼별 로그인
        $platformStats = DB::table('auth_login_histories')
            ->where('created_at', '>=', $startDate)
            ->where('status', 'success')
            ->select(
                'platform',
                DB::raw('COUNT(*) as count'),
                DB::raw('COUNT(DISTINCT user_id) as unique_users')
            )
            ->groupBy('platform')
            ->orderBy('count', 'desc')
            ->get();
        
        // 국가별 로그인
        $countryStats = DB::table('auth_login_histories')
            ->where('created_at', '>=', $startDate)
            ->where('status', 'success')
            ->whereNotNull('country')
            ->select(
                'country',
                DB::raw('COUNT(*) as count'),
                DB::raw('COUNT(DISTINCT user_id) as unique_users')
            )
            ->groupBy('country')
            ->orderBy('count', 'desc')
            ->limit(20)
            ->get();
        
        // 평균 세션 시간
        $sessionStats = DB::table('auth_user_sessions')
            ->where('created_at', '>=', $startDate)
            ->whereNotNull('ended_at')
            ->select(
                DB::raw('AVG(TIMESTAMPDIFF(MINUTE, created_at, ended_at)) as avg_duration'),
                DB::raw('MAX(TIMESTAMPDIFF(MINUTE, created_at, ended_at)) as max_duration'),
                DB::raw('MIN(TIMESTAMPDIFF(MINUTE, created_at, ended_at)) as min_duration')
            )
            ->first();
        
        // 재방문율
        $returningUsers = DB::table('auth_login_histories')
            ->where('created_at', '>=', $startDate)
            ->where('status', 'success')
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > 1')
            ->count(DB::raw('DISTINCT user_id'));
        
        $totalUniqueUsers = DB::table('auth_login_histories')
            ->where('created_at', '>=', $startDate)
            ->where('status', 'success')
            ->count(DB::raw('DISTINCT user_id'));
        
        $returnRate = $totalUniqueUsers > 0 ? round(($returningUsers / $totalUniqueUsers) * 100, 2) : 0;
        
        // 비정상 로그인 패턴
        $suspiciousPatterns = DB::table('auth_login_histories')
            ->where('created_at', '>=', $startDate)
            ->where(function($query) {
                $query->where('is_suspicious', true)
                    ->orWhere('status', 'blocked')
                    ->orWhereNotNull('failed_reason');
            })
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        // 요약 통계
        $summary = [
            'total_logins' => DB::table('auth_login_histories')
                ->where('created_at', '>=', $startDate)
                ->count(),
            'success_rate' => DB::table('auth_login_histories')
                ->where('created_at', '>=', $startDate)
                ->where('status', 'success')
                ->count() / max(DB::table('auth_login_histories')
                ->where('created_at', '>=', $startDate)
                ->count(), 1) * 100,
            'unique_users' => $totalUniqueUsers,
            'returning_users' => $returningUsers,
            'return_rate' => $returnRate,
            'avg_session_duration' => $sessionStats->avg_duration ?? 0,
            'suspicious_attempts' => DB::table('auth_login_histories')
                ->where('created_at', '>=', $startDate)
                ->where('is_suspicious', true)
                ->count(),
        ];
        
        return view('jiny-auth::admin.statistics.login-patterns', compact(
            'loginResults',
            'authMethods',
            'browserStats',
            'platformStats',
            'countryStats',
            'sessionStats',
            'suspiciousPatterns',
            'summary'
        ));
    }
    
    /**
     * 사용자 유지율
     * GET /admin/auth/statistics/retention
     */
    public function retention(Request $request)
    {
        $cohortSize = $request->get('cohort_size', '30'); // 코호트 크기 (일)
        
        // 코호트 분석 - 최근 6개 코호트
        $cohorts = [];
        for ($i = 5; $i >= 0; $i--) {
            $cohortStart = now()->subDays($cohortSize * ($i + 1));
            $cohortEnd = now()->subDays($cohortSize * $i);
            
            // 해당 기간에 가입한 사용자
            $cohortUsers = User::whereBetween('created_at', [$cohortStart, $cohortEnd])
                ->pluck('id');
            
            if ($cohortUsers->isEmpty()) {
                continue;
            }
            
            $cohortData = [
                'period' => $cohortStart->format('Y-m-d') . ' ~ ' . $cohortEnd->format('Y-m-d'),
                'users' => count($cohortUsers),
                'retention' => []
            ];
            
            // 각 기간별 유지율 계산
            for ($day = 1; $day <= 7; $day++) {
                $checkDate = $cohortEnd->copy()->addDays($day);
                
                if ($checkDate > now()) {
                    break;
                }
                
                $activeUsers = DB::table('auth_login_histories')
                    ->whereIn('user_id', $cohortUsers)
                    ->whereDate('created_at', $checkDate)
                    ->where('status', 'success')
                    ->count(DB::raw('DISTINCT user_id'));
                
                $cohortData['retention']["day_{$day}"] = [
                    'count' => $activeUsers,
                    'rate' => round(($activeUsers / count($cohortUsers)) * 100, 2)
                ];
            }
            
            // 주별 유지율
            for ($week = 1; $week <= 4; $week++) {
                $weekStart = $cohortEnd->copy()->addWeeks($week - 1);
                $weekEnd = $cohortEnd->copy()->addWeeks($week);
                
                if ($weekStart > now()) {
                    break;
                }
                
                $activeUsers = DB::table('auth_login_histories')
                    ->whereIn('user_id', $cohortUsers)
                    ->whereBetween('created_at', [$weekStart, min($weekEnd, now())])
                    ->where('status', 'success')
                    ->count(DB::raw('DISTINCT user_id'));
                
                $cohortData['retention']["week_{$week}"] = [
                    'count' => $activeUsers,
                    'rate' => round(($activeUsers / count($cohortUsers)) * 100, 2)
                ];
            }
            
            $cohorts[] = $cohortData;
        }
        
        // 이탈 사용자 분석
        $churnAnalysis = [
            'last_7_days' => User::where('created_at', '<=', now()->subDays(7))
                ->whereNotIn('id', function($query) {
                    $query->select('user_id')
                        ->from('auth_login_histories')
                        ->where('created_at', '>=', now()->subDays(7))
                        ->where('status', 'success');
                })->count(),
            'last_30_days' => User::where('created_at', '<=', now()->subDays(30))
                ->whereNotIn('id', function($query) {
                    $query->select('user_id')
                        ->from('auth_login_histories')
                        ->where('created_at', '>=', now()->subDays(30))
                        ->where('status', 'success');
                })->count(),
            'last_90_days' => User::where('created_at', '<=', now()->subDays(90))
                ->whereNotIn('id', function($query) {
                    $query->select('user_id')
                        ->from('auth_login_histories')
                        ->where('created_at', '>=', now()->subDays(90))
                        ->where('status', 'success');
                })->count(),
        ];
        
        // 재활성화 사용자
        $reactivatedUsers = DB::table('auth_login_histories as l1')
            ->join('auth_login_histories as l2', 'l1.user_id', '=', 'l2.user_id')
            ->where('l1.created_at', '>=', now()->subDays(7))
            ->where('l1.status', 'success')
            ->where('l2.created_at', '<', now()->subDays(30))
            ->where('l2.status', 'success')
            ->whereNotExists(function($query) {
                $query->select(DB::raw(1))
                    ->from('auth_login_histories as l3')
                    ->whereColumn('l3.user_id', 'l1.user_id')
                    ->whereBetween('l3.created_at', [now()->subDays(30), now()->subDays(7)]);
            })
            ->count(DB::raw('DISTINCT l1.user_id'));
        
        // LTV (Lifetime Value) 예측 - 간단한 버전
        $avgUserLifetime = DB::table('users')
            ->whereNotNull('deleted_at')
            ->select(DB::raw('AVG(DATEDIFF(deleted_at, created_at)) as avg_days'))
            ->first();
        
        // 요약
        $summary = [
            'total_users' => User::count(),
            'active_users_7d' => DB::table('auth_login_histories')
                ->where('created_at', '>=', now()->subDays(7))
                ->where('status', 'success')
                ->count(DB::raw('DISTINCT user_id')),
            'active_users_30d' => DB::table('auth_login_histories')
                ->where('created_at', '>=', now()->subDays(30))
                ->where('status', 'success')
                ->count(DB::raw('DISTINCT user_id')),
            'churned_users' => $churnAnalysis['last_30_days'],
            'reactivated_users' => $reactivatedUsers,
            'avg_lifetime_days' => $avgUserLifetime->avg_days ?? 0,
        ];
        
        return view('jiny-auth::admin.statistics.retention', compact(
            'cohorts',
            'churnAnalysis',
            'summary'
        ));
    }
}