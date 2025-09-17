<?php

namespace Jiny\Auth\App\Http\Controllers\Admin\AuthAccountLogs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Admin\App\Services\JsonConfigService;
use Jiny\Auth\App\Models\AccountLog;
use Illuminate\Support\Facades\DB;

/**
 * AuthAccountLogsDelete Controller
 *
 * 회원 활동 로그 삭제 (관리자 전용)
 */
class AuthAccountLogsDelete extends Controller
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
     * Remove the specified resource from storage.
     */
    public function __invoke(Request $request, $id)
    {
        if (! $this->jsonData) {
            return response()->json(['error' => 'JSON 데이터를 로드할 수 없습니다.'], 500);
        }

        // 트랜잭션 사용 여부 확인
        $useTransaction = $this->jsonData['destroy']['enableTransaction'] ?? true;

        try {
            if ($useTransaction) {
                DB::beginTransaction();
            }

            // 모델 조회
            $model = $this->jsonData['table']['model'] ?? AccountLog::class;
            $data = $model::findOrFail($id);

            // 삭제 전 로그 백업 (선택적)
            $this->backupLogBeforeDelete($data);

            // 삭제
            $data->delete();

            if ($useTransaction) {
                DB::commit();
            }

            // 성공 메시지
            $message = $this->jsonData['destroy']['messages']['success'] ?? '로그가 성공적으로 삭제되었습니다.';
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'redirect' => route('admin.auth.account.logs')
                ]);
            }

            return redirect()->route('admin.auth.account.logs')
                ->with('success', $message);

        } catch (\Exception $e) {
            if ($useTransaction) {
                DB::rollBack();
            }

            $errorMessage = sprintf(
                $this->jsonData['destroy']['messages']['error'] ?? '로그 삭제 중 오류가 발생했습니다: %s',
                $e->getMessage()
            );

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 500);
            }

            return redirect()->back()
                ->withErrors(['error' => $errorMessage]);
        }
    }

    /**
     * Hook: 삭제 전 처리
     * 중요한 로그는 삭제를 방지하거나 백업
     */
    public function hookDeleting($wire, $id)
    {
        $log = AccountLog::find($id);
        
        if (!$log) {
            return false;
        }
        
        // 중요한 로그 삭제 방지
        $protectedActions = ['account_created', 'account_deleted', 'permission_changed', 'security_breach'];
        
        if (in_array($log->action, $protectedActions)) {
            $wire->addError('delete', '보안상 중요한 로그는 삭제할 수 없습니다.');
            return false; // 삭제 취소
        }
        
        // 30일 이내의 로그는 삭제 방지
        if ($log->performed_at && $log->performed_at->diffInDays(now()) < 30) {
            $wire->addError('delete', '30일 이내의 로그는 삭제할 수 없습니다.');
            return false; // 삭제 취소
        }
        
        // 삭제 가능
        return true;
    }

    /**
     * Hook: 삭제 후 처리
     */
    public function hookDeleted($wire, $id)
    {
        // 삭제 이력 기록 (감사 로그)
        $this->logDeletion($id);
        
        // 캐시 클리어
        if (function_exists('cache')) {
            cache()->forget('account_logs_count');
            cache()->forget('account_logs_stats');
        }
    }

    /**
     * 삭제 전 로그 백업
     */
    private function backupLogBeforeDelete($log)
    {
        // 백업 테이블이나 파일에 저장
        // 예: deleted_logs 테이블에 저장
        DB::table('deleted_account_logs')->insert([
            'original_id' => $log->id,
            'account_id' => $log->account_id,
            'action' => $log->action,
            'description' => $log->description,
            'ip_address' => $log->ip_address,
            'user_agent' => $log->user_agent,
            'old_values' => json_encode($log->old_values),
            'new_values' => json_encode($log->new_values),
            'meta' => json_encode($log->meta),
            'status' => $log->status,
            'error_message' => $log->error_message,
            'performed_at' => $log->performed_at,
            'deleted_by' => auth()->id(),
            'deleted_at' => now(),
        ]);
    }

    /**
     * 삭제 이력 기록
     */
    private function logDeletion($id)
    {
        // 감사 로그에 삭제 기록
        if (auth()->check()) {
            AccountLog::logAction(auth()->id(), 'log_deleted', [
                'description' => "Account log #{$id} was deleted by administrator",
                'meta' => [
                    'deleted_log_id' => $id,
                    'deleted_by' => auth()->user()->email,
                    'deleted_at' => now()->toIso8601String(),
                ]
            ]);
        }
    }
}