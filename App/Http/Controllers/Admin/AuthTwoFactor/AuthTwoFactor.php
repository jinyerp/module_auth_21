<?php

namespace Jiny\Auth\App\Http\Controllers\Admin\AuthTwoFactor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Jiny\Auth\App\Models\TwoFactorAuth;
use Jiny\Auth\App\Models\Account;
use Carbon\Carbon;

class AuthTwoFactor extends Controller
{
    private $jsonData;

    public function __construct()
    {
        $this->jsonData = $this->loadJsonData();
    }

    private function loadJsonData()
    {
        $jsonPath = __DIR__ . '/AuthTwoFactor.json';
        if (file_exists($jsonPath)) {
            $jsonContent = file_get_contents($jsonPath);
            return json_decode($jsonContent, true);
        }
        return [];
    }

    public function index(Request $request)
    {
        // JSON 설정에 컨트롤러 클래스 추가 (Hook 시스템 활성화)
        $this->jsonData['controllerClass'] = self::class;
        
        // 템플릿 경로 설정
        $this->jsonData['template'] = [
            'list' => 'jiny-admin::crud.index',
            'table' => 'jiny-auth::admin.auth_two_factor.table'
        ];

        // 통계 데이터 생성
        $statistics = $this->generateStatistics();

        return view('jiny-admin::crud.index', [
            'jsonData' => $this->jsonData,
            'statistics' => $statistics
        ]);
    }

    /**
     * 2FA 통계 데이터 생성
     */
    private function generateStatistics()
    {
        $stats = [];
        
        // 전체 사용자 수
        $stats['total_users'] = Account::count();
        
        // 2FA 활성화 사용자 수
        $stats['enabled_users'] = TwoFactorAuth::where('enabled', true)->count();
        
        // 방법별 사용자 수
        $stats['by_method'] = TwoFactorAuth::where('enabled', true)
            ->select('method', DB::raw('count(*) as count'))
            ->groupBy('method')
            ->pluck('count', 'method')
            ->toArray();
        
        // 최근 7일간 2FA 사용 횟수
        $stats['recent_usage'] = TwoFactorAuth::where('last_used_at', '>=', Carbon::now()->subDays(7))
            ->count();
        
        // 실패 시도가 많은 계정 수 (5회 이상)
        $stats['high_failed_attempts'] = TwoFactorAuth::where('failed_attempts', '>=', 5)
            ->count();
        
        // 활성화 비율
        $stats['enabled_percentage'] = $stats['total_users'] > 0 
            ? round(($stats['enabled_users'] / $stats['total_users']) * 100, 1)
            : 0;
        
        return $stats;
    }

    /**
     * Hook: 목록 조회 전 처리
     */
    public function hookIndexing($livewire)
    {
        // 사용자 정보와 함께 조회하도록 설정
        $query = TwoFactorAuth::with('account:id,name,email');
        
        // 검색 조건이 있을 경우
        if (!empty($livewire->search)) {
            $query->whereHas('account', function($q) use ($livewire) {
                $q->where('name', 'like', '%' . $livewire->search . '%')
                  ->orWhere('email', 'like', '%' . $livewire->search . '%');
            });
        }
        
        // 활성화 상태 필터
        if (isset($livewire->filters['enabled'])) {
            $query->where('enabled', $livewire->filters['enabled']);
        }
        
        // 방법 필터
        if (isset($livewire->filters['method'])) {
            $query->where('method', $livewire->filters['method']);
        }
        
        return $query;
    }

    /**
     * Hook: 목록 데이터 가공
     */
    public function hookIndexed($livewire, $rows)
    {
        foreach ($rows as $row) {
            // 사용자 정보 추가
            if ($row->account) {
                $row->user_display = $row->account->name . ' (' . $row->account->email . ')';
            } else {
                $row->user_display = '삭제된 사용자';
            }
            
            // 상태 라벨
            $row->status_label = $row->enabled ? '활성화' : '비활성화';
            $row->status_color = $row->enabled ? 'green' : 'gray';
            
            // 방법 라벨
            $methodLabels = [
                'totp' => 'TOTP (앱)',
                'sms' => 'SMS',
                'email' => '이메일'
            ];
            $row->method_label = $methodLabels[$row->method] ?? $row->method;
            
            // 마지막 사용 시간 포맷
            if ($row->last_used_at) {
                $lastUsed = Carbon::parse($row->last_used_at);
                if ($lastUsed->isToday()) {
                    $row->last_used_display = '오늘 ' . $lastUsed->format('H:i');
                } elseif ($lastUsed->isYesterday()) {
                    $row->last_used_display = '어제 ' . $lastUsed->format('H:i');
                } else {
                    $row->last_used_display = $lastUsed->format('m/d H:i');
                }
            } else {
                $row->last_used_display = '사용 기록 없음';
            }
            
            // 실패 시도 경고
            if ($row->failed_attempts >= 5) {
                $row->has_warning = true;
                $row->warning_message = '실패 시도 ' . $row->failed_attempts . '회';
            }
        }
        
        return $rows;
    }

    /**
     * 대량 비활성화
     */
    public function bulkDisable(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:two_factor_auths,id'
        ]);

        $count = TwoFactorAuth::whereIn('id', $request->ids)
            ->update([
                'enabled' => false,
                'enabled_at' => null,
                'failed_attempts' => 0
            ]);

        return response()->json([
            'success' => true,
            'message' => $count . '개의 2FA가 비활성화되었습니다.'
        ]);
    }

    /**
     * Hook: 통계 데이터 생성
     */
    public function hookStatistics($livewire)
    {
        return $this->generateStatistics();
    }
}