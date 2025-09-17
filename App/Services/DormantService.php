<?php

namespace Jiny\Auth\App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Jiny\Auth\App\Models\User;
use Jiny\Auth\App\Models\UserDormantAccount;
use Jiny\Auth\App\Models\UserDormantPolicy;
use Jiny\Auth\App\Models\UserDormantLog;

/**
 * DormantService
 *
 * 휴면회원 관리 서비스
 * 휴면 처리, 통계 조회 등의 비즈니스 로직을 담당
 *
 * @package Jiny\Auth\App\Services
 * @author JinyPHP
 * @version 1.0.0
 * @since 1.0.0
 * @license MIT
 */
class DormantService
{
    /**
     * 휴면 계정 처리
     */
    public function processDormantAccounts()
    {
        try {
            $processed = 0;
            $errors = [];

            // 활성 정책들 조회
            $policies = UserDormantPolicy::active()->get();

            foreach ($policies as $policy) {
                // 정책에 해당하는 사용자들 조회
                $users = $this->getUsersForDormantProcessing($policy);

                foreach ($users as $user) {
                    try {
                        // 이미 휴면 계정이 있는지 확인
                        $existingDormant = UserDormantAccount::where('user_id', $user->id)
                            ->where('status', UserDormantAccount::STATUS_DORMANT)
                            ->first();

                        if ($existingDormant) {
                            continue; // 이미 휴면 상태
                        }

                        // 휴면 계정 생성
                        $dormantAccount = UserDormantAccount::create([
                            'user_id' => $user->id,
                            'policy_id' => $policy->id,
                            'status' => UserDormantAccount::STATUS_DORMANT,
                            'dormant_at' => now(),
                            'expired_at' => now()->addDays($policy->expiry_days),
                            'dormant_days' => $policy->inactive_days,
                            'last_activity_at' => $user->last_login_at,
                            'creator_id' => auth()->id(),
                        ]);

                        // 사용자 상태 업데이트
                        $user->update([
                            'is_dormant' => true,
                            'status' => 'dormant'
                        ]);

                        // 로그 기록
                        UserDormantLog::logActivity(
                            $dormantAccount->id,
                            $dormantAccount->user_id,
                            UserDormantLog::ACTION_AUTO_DORMANT,
                            "자동 휴면 처리",
                            "정책 '{$policy->name}'에 의한 자동 휴면 처리",
                            auth()->id(),
                            UserDormantLog::PERFORMER_TYPE_SYSTEM,
                            null,
                            null,
                            null,
                            $dormantAccount->policy_id
                        );

                        $processed++;

                    } catch (\Exception $e) {
                        $errors[] = "사용자 ID {$user->id}: " . $e->getMessage();
                        Log::error('휴면 처리 중 오류', [
                            'user_id' => $user->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }

            return [
                'processed' => $processed,
                'errors' => $errors,
                'success' => count($errors) === 0
            ];

        } catch (\Exception $e) {
            Log::error('휴면 처리 서비스 오류', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * 휴면 처리 대상 사용자 조회
     */
    private function getUsersForDormantProcessing($policy)
    {
        $inactiveDays = $policy->inactive_days;
        $cutoffDate = now()->subDays($inactiveDays);

        return User::where('is_dormant', false)
            ->where('status', 'active')
            ->where(function($query) use ($cutoffDate) {
                $query->where('last_login_at', '<', $cutoffDate)
                      ->orWhereNull('last_login_at');
            })
            ->whereNotExists(function($query) {
                $query->select(DB::raw(1))
                      ->from('user_dormant_accounts')
                      ->whereRaw('user_dormant_accounts.user_id = users.id')
                      ->where('user_dormant_accounts.status', UserDormantAccount::STATUS_DORMANT);
            })
            ->get();
    }

    /**
     * 휴면 통계 조회
     */
    public function getDormantStatistics($filters = [])
    {
        try {
            $query = UserDormantAccount::query();

            // 필터 적용
            if (!empty($filters['date_from'])) {
                $query->where('created_at', '>=', $filters['date_from']);
            }

            if (!empty($filters['date_to'])) {
                $query->where('created_at', '<=', $filters['date_to']);
            }

            if (!empty($filters['policy_id'])) {
                $query->where('policy_id', $filters['policy_id']);
            }

            // 기본 통계
            $total = $query->count();
            $dormant = (clone $query)->where('status', UserDormantAccount::STATUS_DORMANT)->count();
            $expired = (clone $query)->where('status', UserDormantAccount::STATUS_EXPIRED)->count();
            $restored = (clone $query)->where('status', UserDormantAccount::STATUS_RESTORED)->count();

            // 정책별 통계
            $policyStats = UserDormantPolicy::withCount(['dormantAccounts' => function($q) use ($filters) {
                if (!empty($filters['date_from'])) {
                    $q->where('created_at', '>=', $filters['date_from']);
                }
                if (!empty($filters['date_to'])) {
                    $q->where('created_at', '<=', $filters['date_to']);
                }
            }])->get();

            // 월별 통계
            $monthlyStats = $this->getMonthlyStatistics($filters);

            return [
                'total' => $total,
                'dormant' => $dormant,
                'expired' => $expired,
                'restored' => $restored,
                'policy_stats' => $policyStats,
                'monthly_stats' => $monthlyStats,
                'filters' => $filters
            ];

        } catch (\Exception $e) {
            Log::error('휴면 통계 조회 중 오류', [
                'error' => $e->getMessage(),
                'filters' => $filters
            ]);

            throw $e;
        }
    }

    /**
     * 월별 통계 조회
     */
    private function getMonthlyStatistics($filters = [])
    {
        $months = [];
        $dormantCounts = [];
        $restoredCounts = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = $date->format('Y-m');

            $dormantQuery = UserDormantAccount::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month);

            $restoredQuery = UserDormantAccount::where('status', UserDormantAccount::STATUS_RESTORED)
                ->whereYear('restored_at', $date->year)
                ->whereMonth('restored_at', $date->month);

            // 필터 적용
            if (!empty($filters['policy_id'])) {
                $dormantQuery->where('policy_id', $filters['policy_id']);
                $restoredQuery->where('policy_id', $filters['policy_id']);
            }

            $dormantCounts[] = $dormantQuery->count();
            $restoredCounts[] = $restoredQuery->count();
        }

        return [
            'months' => $months,
            'dormant_counts' => $dormantCounts,
            'restored_counts' => $restoredCounts,
        ];
    }

    /**
     * 만료된 휴면 계정 처리
     */
    public function processExpiredAccounts()
    {
        try {
            $expiredAccounts = UserDormantAccount::where('status', UserDormantAccount::STATUS_DORMANT)
                ->where('expired_at', '<=', now())
                ->get();

            $processed = 0;

            foreach ($expiredAccounts as $account) {
                try {
                    $account->update([
                        'status' => UserDormantAccount::STATUS_EXPIRED
                    ]);

                    // 로그 기록
                    UserDormantLog::logActivity(
                        $account->id,
                        $account->user_id,
                        UserDormantLog::ACTION_EXPIRED,
                        "계정 만료",
                        "휴면 계정 만료 처리",
                        auth()->id(),
                        UserDormantLog::PERFORMER_TYPE_SYSTEM,
                        null,
                        null,
                        null,
                        $account->policy_id
                    );

                    $processed++;

                } catch (\Exception $e) {
                    Log::error('만료 처리 중 오류', [
                        'account_id' => $account->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            return [
                'processed' => $processed,
                'success' => true
            ];

        } catch (\Exception $e) {
            Log::error('만료 처리 서비스 오류', [
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }
}
