<?php

namespace Jiny\Auth\App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Jiny\Auth\App\Models\UserApproval;
use Jiny\Auth\App\Models\Account;

/**
 * 사용자 승인 관리 컨트롤러
 *
 * 회원가입 후 관리자 승인이 필요한 사용자들을 위한 승인 대기 페이지를 제공합니다.
 * 승인 상태 확인, 승인 요청 재제출 등의 기능을 담당합니다.
 *
 * 비즈니스 컨텍스트:
 * - 승인 대기 사용자 관리
 * - 승인 상태 실시간 확인
 * - 승인 요청 재제출 처리
 * - 사용자 경험 개선
 *
 * 도메인 컨텍스트:
 * - 승인 워크플로우 관리
 * - 사용자 상태 추적
 * - 관리자 승인 프로세스
 * - 보안 및 감사
 *
 * @package Jiny\Auth\Http\Controllers\Auth
 * @author JinyPHP
 * @version 1.0.0
 * @since 1.0.0
 * @license MIT
 */
class AuthApprovalController extends Controller
{
    /**
     * 승인 대기 페이지 표시
     *
     * 회원가입 후 승인이 필요한 사용자가 접근하는 대기 페이지입니다.
     * 사용자의 승인 상태와 요청 정보를 표시하며, 실시간으로 승인 상태를 확인할 수 있습니다.
     *
     * 비즈니스 규칙:
     * - 로그인하지 않은 사용자는 로그인 페이지로 리다이렉트
     * - 승인된 사용자는 로그인 페이지로 리다이렉트
     * - 승인 대기 중인 사용자만 페이지 접근 허용
     *
     * 보안 고려사항:
     * - 인증된 사용자만 접근 가능
     * - 자신의 승인 정보만 조회 가능
     * - 세션 기반 인증 사용
     *
     * @param Request $request HTTP 요청 객체
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function showApprovalPage(Request $request)
    {
        // 1. 인증 확인: 로그인한 사용자 정보 가져오기
        $user = Auth::user();

        // 2. 인증 검증: 로그인하지 않은 경우 로그인 페이지로 리다이렉트
        if (!$user) {
            return redirect()->route('login');
        }

        // 3. 승인 상태 확인: 사용자의 최신 승인 요청 정보 가져오기
        $latestApproval = UserApproval::with(['logs.performedBy', 'approver'])
            ->where('user_id', $user->id)
            ->latest()
            ->first();

        // 4. 승인된 사용자는 로그인 페이지로 리다이렉트
        if ($user->is_approved) {
            return redirect()->route('login');
        }

        // 5. 승인 이력 가져오기
        $approvalHistory = $latestApproval ? $latestApproval->getApprovalHistory() : collect();

        // 6. 뷰 반환: 승인 대기 페이지와 사용자 정보 전달
        return view('jiny-auth::auth.approval', [
            'user' => $user,
            'approval' => $latestApproval,
            'approvalHistory' => $approvalHistory,
            'workflowStep' => $latestApproval ? $latestApproval->getWorkflowStep() : 0,
            'workflowDescription' => $latestApproval ? $latestApproval->getWorkflowStepDescription() : '승인 요청 없음'
        ]);
    }

    /**
     * 승인 상태 확인 (AJAX)
     *
     * 사용자의 승인 상태를 실시간으로 확인하는 AJAX 엔드포인트입니다.
     * 승인 대기 페이지에서 주기적으로 호출되어 승인 완료 시 자동으로 리다이렉트합니다.
     *
     * 비즈니스 규칙:
     * - 승인된 사용자는 로그인 페이지로 리다이렉트
     * - 승인 대기 중인 사용자는 대기 상태 유지
     * - 실시간 상태 업데이트를 위해 사용자 정보 새로고침
     *
     * 성능 고려사항:
     * - 데이터베이스 쿼리 최소화
     * - 캐시된 사용자 정보 새로고침
     * - AJAX 응답 최적화
     *
     * 보안 고려사항:
     * - 인증된 사용자만 접근 가능
     * - CSRF 보호 적용
     * - JSON 응답으로 XSS 방지
     *
     * @param Request $request HTTP 요청 객체
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkApprovalStatus(Request $request)
    {
        // 1. AJAX 요청 확인 (GET과 POST 모두 허용)
        if (!$request->ajax() && !$request->wantsJson() && $request->method() !== 'GET') {
            return response()->json([
                'success' => false,
                'message' => 'AJAX 요청만 허용됩니다.'
            ], 400);
        }

        // 2. 인증 확인: 로그인한 사용자 정보 가져오기
        $user = Auth::user();

        // 3. 인증 검증: 로그인하지 않은 경우 401 오류 반환
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => '로그인이 필요합니다.'
            ], 401);
        }

        // 4. 최신 승인 요청 상태 확인
        $latestApproval = UserApproval::where('user_id', $user->id)
            ->latest()
            ->first();

        // 5. 승인 상태 확인 및 응답
        if ($user->is_approved) {
            // 승인 완료: 로그인 페이지로 리다이렉트 정보 포함
            return response()->json([
                'success' => true,
                'approved' => true,
                'message' => '승인이 완료되었습니다.',
                'redirect' => route('login'),
                'workflow_step' => 3,
                'workflow_description' => '승인 완료'
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } elseif ($latestApproval && $latestApproval->isDenied()) {
            // 거부됨: 거부 상태 반환
            $isReturned = $latestApproval->isReturned();
            $statusKey = $isReturned ? 'returned' : 'rejected';
            $message = $isReturned ? '재신청이 거부되었습니다.' : '승인이 거부되었습니다.';

            return response()->json([
                'success' => true,
                'approved' => false,
                $statusKey => true,
                'message' => $message,
                'rejection_reason' => $latestApproval->rejection_reason,
                'workflow_step' => $latestApproval->getWorkflowStep(),
                'workflow_description' => $latestApproval->getWorkflowStepDescription(),
                'can_resubmit' => $latestApproval->canResubmit()
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } else {
            // 승인 대기: 대기 상태 유지
            $workflowStep = $latestApproval ? $latestApproval->getWorkflowStep() : 0;
            $workflowDescription = $latestApproval ? $latestApproval->getWorkflowStepDescription() : '승인 요청 없음';

            return response()->json([
                'success' => true,
                'approved' => false,
                'rejected' => false,
                'returned' => false,
                'message' => '아직 승인 대기 중입니다.',
                'workflow_step' => $workflowStep,
                'workflow_description' => $workflowDescription
            ], 200, [], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * 승인 요청 재제출
     *
     * 사용자가 승인 요청을 다시 제출할 때 사용하는 엔드포인트입니다.
     * 기존 승인 요청이 만료되었거나 문제가 있는 경우 새로운 승인 요청을 생성합니다.
     *
     * 비즈니스 규칙:
     * - 기존 승인 요청이 있으면 중복 제출 방지
     * - 새로운 승인 요청은 'resubmitted' 상태로 생성
     * - 재제출 시점과 IP 정보 기록
     *
     * 데이터 무결성:
     * - 사용자 정보 검증
     * - 중복 요청 방지
     * - 감사 로그 기록
     *
     * 보안 고려사항:
     * - 인증된 사용자만 접근 가능
     * - IP 주소와 사용자 에이전트 기록
     * - 재제출 시점 추적
     *
     * @param Request $request HTTP 요청 객체
     * @return \Illuminate\Http\JsonResponse
     */
    public function resubmitApproval(Request $request)
    {
        // 1. 인증 확인: 로그인한 사용자 정보 가져오기
        $user = Auth::user();

        // 2. 인증 검증: 로그인하지 않은 경우 401 오류 반환
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => '로그인이 필요합니다.'
            ], 401);
        }

        // 3. AJAX 요청 확인
        if (!$request->ajax() && !$request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'AJAX 요청만 허용됩니다.'
            ], 400);
        }

        try {
            DB::beginTransaction();

            // 4. 기존 승인 요청 확인 (pending 또는 resubmitted 상태)
            $existingWaitingApproval = UserApproval::where('user_id', $user->id)
                ->whereIn('status', ['pending', 'resubmitted'])
                ->first();

            // 5. 중복 방지: 이미 진행 중인 승인 요청이 있으면 오류 반환
            if ($existingWaitingApproval) {
                return response()->json([
                    'success' => false,
                    'message' => '이미 승인 요청이 진행 중입니다.'
                ]);
            }

            // 6. 최신 거부/반려된 승인 요청 찾기
            $latestDeniedApproval = UserApproval::where('user_id', $user->id)
                ->whereIn('status', ['rejected', 'returned'])
                ->latest()
                ->first();

            if (!$latestDeniedApproval) {
                return response()->json([
                    'success' => false,
                    'message' => '재신청할 거부/반려된 승인 요청이 없습니다.'
                ]);
            }

            // 7. 재신청 가능 여부 확인
            if (!$latestDeniedApproval->canResubmit()) {
                return response()->json([
                    'success' => false,
                    'message' => '재신청할 수 없는 상태입니다.'
                ]);
            }

            Log::info('재승인 요청 시작', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'denied_approval_id' => $latestDeniedApproval->id,
                'previous_status' => $latestDeniedApproval->status,
                'request_ip' => $request->ip()
            ]);

            // 8. 새로운 승인 요청 생성 (재신청)
            $newApproval = UserApproval::create([
                'uuid' => \Illuminate\Support\Str::uuid(),
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'status' => 'resubmitted',
                'request_ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'request_data' => array_merge($latestDeniedApproval->request_data ?? [], [
                    'resubmitted_at' => now()->toISOString(),
                    'previous_approval_id' => $latestDeniedApproval->id,
                    'resubmission_count' => ($latestDeniedApproval->resubmission_count ?? 0) + 1
                ]),
                'previous_approval_id' => $latestDeniedApproval->id,
                'resubmission_count' => ($latestDeniedApproval->resubmission_count ?? 0) + 1,
                'last_resubmitted_at' => now()
            ]);

            Log::info('재승인 요청 생성 완료', [
                'new_approval_id' => $newApproval->id,
                'user_id' => $user->id,
                'previous_approval_id' => $latestDeniedApproval->id,
                'resubmission_count' => $newApproval->resubmission_count
            ]);

            // 9. 승인 로그 기록 (재신청)
            \Jiny\Auth\App\Models\UserApprovalLog::logResubmission(
                $user->id,
                $newApproval->id,
                '재승인 요청 제출'
            );

            Log::info('재승인 로그 기록 완료', [
                'approval_id' => $newApproval->id,
                'user_id' => $user->id
            ]);

            DB::commit();

            // 10. 성공 응답
            return response()->json([
                'success' => true,
                'message' => '승인 요청이 재제출되었습니다.',
                'approval_id' => $newApproval->id,
                'workflow_step' => $newApproval->getWorkflowStep(),
                'workflow_description' => $newApproval->getWorkflowStepDescription()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('재승인 요청 실패', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => '재승인 요청 처리 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }
}
