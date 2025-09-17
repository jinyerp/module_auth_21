<?php

namespace Jiny\Auth\App\Http\Controllers\Admin\AuthLoginHistory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\App\Models\LoginHistory;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AuthLoginHistoryDelete extends Controller
{
    protected $jsonData;

    public function __construct()
    {
        $this->loadJsonData();
    }

    private function loadJsonData()
    {
        $jsonPath = __DIR__ . '/AuthLoginHistory.json';
        if (file_exists($jsonPath)) {
            $this->jsonData = json_decode(file_get_contents($jsonPath), true);
            $this->jsonData['controllerClass'] = self::class;
        } else {
            $this->jsonData = [
                'title' => '로그인 기록 삭제',
                'controllerClass' => self::class
            ];
        }
    }

    public function destroy($id)
    {
        // JSON 설정에 따라 삭제 권한 확인
        if ($this->jsonData['readonly'] ?? true) {
            return redirect()
                ->route('admin.auth.login-history')
                ->with('error', '로그인 기록은 읽기 전용입니다. 삭제할 수 없습니다.');
        }

        $data = LoginHistory::findOrFail($id);

        // 감사 로그 기록 (삭제 전)
        $this->logDeletion($data);

        $data->delete();

        return redirect()
            ->route('admin.auth.login-history')
            ->with('success', '로그인 기록이 삭제되었습니다.');
    }

    /**
     * Hook: 삭제 전 처리
     * 관리자 권한 확인 및 중요 데이터 백업
     */
    public function hookDeleting($wire, $id)
    {
        // 관리자 권한 확인
        if (!auth()->user()->hasRole('super-admin')) {
            return "최고 관리자만 로그인 기록을 삭제할 수 있습니다.";
        }

        $loginHistory = LoginHistory::find($id);
        
        if (!$loginHistory) {
            return "로그인 기록을 찾을 수 없습니다.";
        }

        // 현재 활성 세션은 삭제 불가
        if (!$loginHistory->logout_at) {
            return "활성 세션은 삭제할 수 없습니다. 먼저 세션을 종료하세요.";
        }

        // 최근 7일 이내 기록은 보존
        $loginDate = Carbon::parse($loginHistory->login_at);
        if ($loginDate->diffInDays(Carbon::now()) < 7) {
            return "최근 7일 이내의 로그인 기록은 삭제할 수 없습니다.";
        }

        // 백업 테이블에 복사
        $this->backupLoginHistory($loginHistory);

        // 삭제 허용
        return true;
    }

    /**
     * Hook: 삭제 후 처리
     */
    public function hookDeleted($wire, $id)
    {
        // 관련 데이터 정리
        $this->cleanupRelatedData($id);

        // 캐시 클리어
        cache()->forget("login_history_{$id}");

        // 통계 업데이트
        $this->updateStatistics();
    }

    /**
     * 로그인 기록 백업
     */
    private function backupLoginHistory($loginHistory)
    {
        DB::table('login_histories_backup')->insert([
            'original_id' => $loginHistory->id,
            'account_id' => $loginHistory->account_id,
            'login_at' => $loginHistory->login_at,
            'logout_at' => $loginHistory->logout_at,
            'ip_address' => $loginHistory->ip_address,
            'location' => $loginHistory->location,
            'device_type' => $loginHistory->device_type,
            'browser' => $loginHistory->browser,
            'status' => $loginHistory->status,
            'failed_attempts' => $loginHistory->failed_attempts,
            'session_id' => $loginHistory->session_id,
            'deleted_at' => Carbon::now(),
            'deleted_by' => auth()->id(),
            'created_at' => $loginHistory->created_at,
            'updated_at' => $loginHistory->updated_at
        ]);
    }

    /**
     * 삭제 로그 기록
     */
    private function logDeletion($loginHistory)
    {
        activity()
            ->causedBy(auth()->user())
            ->performedOn($loginHistory)
            ->withProperties([
                'deleted_data' => $loginHistory->toArray(),
                'reason' => request()->input('reason', '사유 없음')
            ])
            ->log('로그인 기록 삭제');
    }

    /**
     * 관련 데이터 정리
     */
    private function cleanupRelatedData($id)
    {
        // 관련 알림 삭제
        DB::table('notifications')
            ->where('data->login_history_id', $id)
            ->delete();

        // 관련 활동 로그 업데이트
        DB::table('activity_log')
            ->where('subject_type', LoginHistory::class)
            ->where('subject_id', $id)
            ->update(['properties->deleted' => true]);
    }

    /**
     * 통계 업데이트
     */
    private function updateStatistics()
    {
        // 통계 캐시 재계산
        cache()->forget('login_statistics');
        
        // 일일 통계 업데이트
        $today = Carbon::today();
        $stats = [
            'total_logins' => LoginHistory::whereDate('login_at', $today)->count(),
            'successful_logins' => LoginHistory::whereDate('login_at', $today)->where('status', 'success')->count(),
            'failed_logins' => LoginHistory::whereDate('login_at', $today)->where('status', 'failed')->count(),
        ];
        
        cache()->put('daily_login_stats_' . $today->format('Y-m-d'), $stats, Carbon::tomorrow());
    }

    /**
     * Hook: 대량 삭제 (오래된 기록 정리)
     */
    public function hookBulkDelete($wire, $params)
    {
        // 관리자 권한 확인
        if (!auth()->user()->hasRole('super-admin')) {
            return "최고 관리자만 대량 삭제를 수행할 수 있습니다.";
        }

        $daysOld = $params['days'] ?? 90; // 기본 90일 이상 된 기록 삭제
        
        $cutoffDate = Carbon::now()->subDays($daysOld);
        
        // 삭제할 기록 조회
        $toDelete = LoginHistory::where('login_at', '<', $cutoffDate)
            ->whereNotNull('logout_at') // 종료된 세션만
            ->get();

        // 백업
        foreach ($toDelete as $record) {
            $this->backupLoginHistory($record);
        }

        // 삭제 수행
        $deletedCount = LoginHistory::where('login_at', '<', $cutoffDate)
            ->whereNotNull('logout_at')
            ->delete();

        // 로그 기록
        activity()
            ->causedBy(auth()->user())
            ->withProperties([
                'action' => 'bulk_delete',
                'days_old' => $daysOld,
                'deleted_count' => $deletedCount
            ])
            ->log("로그인 기록 대량 삭제: {$daysOld}일 이상 된 {$deletedCount}개 기록");

        return [
            'success' => true,
            'message' => "{$deletedCount}개의 로그인 기록이 삭제되었습니다."
        ];
    }

    /**
     * Hook: 삭제 가능 여부 확인
     */
    public function hookCanDelete($wire, $model)
    {
        // 읽기 전용 모드 확인
        if ($this->jsonData['readonly'] ?? true) {
            return false;
        }

        // 활성 세션 확인
        if (!$model->logout_at) {
            return false;
        }

        // 보존 기간 확인 (7일)
        $loginDate = Carbon::parse($model->login_at);
        if ($loginDate->diffInDays(Carbon::now()) < 7) {
            return false;
        }

        return true;
    }
}