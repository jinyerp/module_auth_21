<?php

namespace Jiny\Auth\Console\Commands;

use Illuminate\Console\Command;
use Jiny\Auth\Services\DormantService;
use Jiny\Auth\Models\User;

class UserDormantCheckCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:dormant-check {--days=365 : 휴면 전환 기준 일수 (기본: 365일)} {--dry-run : 실제 전환하지 않고 대상만 확인}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '휴면 전환 대상 회원을 휴면 상태로 전환합니다.';

    /**
     * Execute the console command.
     */
    public function handle(DormantService $dormantService): int
    {
        $days = (int) $this->option('days');
        $isDryRun = $this->option('dry-run');

        $this->info("휴면 전환 대상 확인 중... (기준: {$days}일)");

        // 휴면 전환 대상 수 확인
        $candidatesCount = $dormantService->getDormantCandidatesCount($days);

        if ($candidatesCount === 0) {
            $this->info('휴면 전환 대상이 없습니다.');
            return 0;
        }

        $this->info("휴면 전환 대상: {$candidatesCount}명");

        if ($isDryRun) {
            $this->info('DRY RUN 모드: 실제 전환하지 않습니다.');
            return 0;
        }

        // 휴면 전환 실행
        $this->info('휴면 전환을 시작합니다...');

        $result = $dormantService->processDormantUsers($days);

        $this->info("처리 완료:");
        $this->info("- 전환된 회원: {$result['processed_count']}명");
        $this->info("- 총 대상: {$result['total_candidates']}명");

        if (!empty($result['errors'])) {
            $this->warn("- 오류 발생: " . count($result['errors']) . "건");

            foreach ($result['errors'] as $error) {
                $this->error("  - User ID {$error['user_id']} ({$error['email']}): {$error['error']}");
            }
        }

        // 통계 출력
        $stats = $dormantService->getDormantStatistics();
        $this->info("\n현재 통계:");
        $this->info("- 총 회원: {$stats['total_users']}명");
        $this->info("- 활성 회원: {$stats['active_users']}명");
        $this->info("- 휴면 회원: {$stats['dormant_users']}명");
        $this->info("- 휴면 비율: {$stats['dormant_percentage']}%");

        return 0;
    }
}
