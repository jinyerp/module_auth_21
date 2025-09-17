<?php

namespace Jiny\Auth\App\Http\Controllers\Admin;

use Jiny\Admin\App\Http\Controllers\AdminResourceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Jiny\Auth\App\Models\UserTermLog;
use Jiny\Auth\App\Models\UserTerms;

/**
 * AdminAuthTermsLogsController
 *
 * 약관 동의 로그 관리 컨트롤러
 * AdminResourceController를 상속하여 템플릿 메소드 패턴으로 구현
 *
 * @package Jiny\Auth\App\Http\Controllers\Admin
 * @author JinyPHP
 * @version 1.0.0
 * @since 1.0.0
 * @license MIT
 *
 * @docs docs/features/AdminAuthTermsLogs.md
 *
 * 🔄 기능 수정 시 테스트 실행 필요:
 * 이 컨트롤러의 기능이 수정되면 다음 테스트를 반드시 실행해주세요:
 *
 * ```bash
 * # 전체 약관 동의 로그 관리 테스트 실행
 * php artisan test jiny/auth/tests/Feature/AdminAuthTermsLogsControllerTest.php
 * ```
 *
 * 📋 주요 테스트 항목:
 * 1. 약관 동의 로그 CRUD 작업 테스트
 * 2. 동의/거부 상태 변경 테스트
 * 3. 동의 철회 및 재동의 처리 테스트
 * 4. 로그 일괄 삭제 테스트
 * 5. 사용자별 동의 이력 조회 테스트
 * 6. 약관별 동의 통계 조회 테스트
 * 7. 동의 방법별 로그 관리 테스트
 * 8. IP 주소 및 사용자 에이전트 기록 테스트
 * 9. 동의 타입별 로그 분류 테스트
 * 10. 권한 검증 및 보안 테스트
 *
 * ⚠️ 주의사항:
 * - 동의 철회 시 withdrawn_at 타임스탬프 설정 확인
 * - 재동의 시 철회 상태 해제 확인
 * - 동의 상태 변경 시 agreed_at 타임스탬프 업데이트 확인
 * - 로그 삭제 시 감사 로그 기록 확인
 * - 사용자별/약관별 통계 데이터 정확성 확인
 * - 동의 방법별 메타데이터 저장 확인
 */
class AdminAuthTermsLogsController extends AdminResourceController
{
    // 뷰 경로 변수 정의
    public $indexPath = 'jiny-auth::admin.terms-logs.index';
    public $createPath = 'jiny-auth::admin.terms-logs.create';
    public $editPath = 'jiny-auth::admin.terms-logs.edit';
    public $showPath = 'jiny-auth::admin.terms-logs.show';

    protected $filterable = ['user_id', 'term_id', 'agreed', 'version', 'consent_type', 'consent_method', 'ip_address'];
    protected $validFilters = ['user_id', 'term_id', 'agreed', 'version', 'consent_type', 'consent_method', 'ip_address', 'search'];
    protected $sortableColumns = ['user_id', 'term_id', 'agreed', 'version', 'consent_type', 'consent_method', 'agreed_at', 'withdrawn_at', 'created_at', 'updated_at'];

    protected function getTableName() { return 'user_term_logs'; }
    protected function getModuleName() { return 'auth.user-terms-logs'; }

    /**
     * 로그 목록 조회
     *
     * 라우트: GET /terms/logs
     * 기능: 약관 동의 로그 목록을 페이지네이션과 함께 조회
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    protected function _index(Request $request)
    {
        $filters = $this->getFilterParameters($request);
        $perPage = $request->get('per_page', 15);
        $query = UserTermLog::with(['user', 'term']);
        $likeFields = ['ip_address', 'user_agent', 'version'];
        $query = $this->applyFilter($filters, $query, $likeFields);
        $query = $this->sort($query, $request);
        $logs = $query->paginate($perPage);
        return View::make($this->indexPath, [
            'rows' => $logs,
            'filters' => $filters,
            'stats' => $this->getStats()
        ]);
    }

    /**
     * 로그 통계 조회
     *
     * @return array
     */
    private function getStats()
    {
        return [
            'total' => UserTermLog::count(),
            'agreed' => UserTermLog::where('agreed', true)->count(),
            'declined' => UserTermLog::where('agreed', false)->count(),
            'withdrawn' => UserTermLog::whereNotNull('withdrawn_at')->count(),
            'unique_users' => UserTermLog::distinct('user_id')->count('user_id'),
            'unique_terms' => UserTermLog::distinct('term_id')->count('term_id'),
            'initial_consents' => UserTermLog::where('consent_type', 'initial')->count(),
            'reconsents' => UserTermLog::where('consent_type', 'reconsent')->count(),
            'withdrawals' => UserTermLog::where('consent_type', 'withdrawal')->count(),
        ];
    }

    /**
     * 로그 상세 보기
     *
     * 라우트: GET /terms/logs/{id}
     * 기능: 특정 로그의 상세 정보를 조회
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\View\View
     */
    protected function _show(Request $request, $id)
    {
        $item = UserTermLog::with(['user', 'term'])->findOrFail($id);
        return View::make($this->showPath, compact('item'));
    }

    /**
     * 로그 생성 폼
     *
     * 라우트: GET /terms/logs/create
     * 기능: 로그 생성 폼을 표시
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    protected function _create(Request $request)
    {
        return View::make($this->createPath);
    }

    /**
     * 로그 저장
     *
     * 라우트: POST /terms/logs
     * 기능: 새로운 로그를 생성하고 저장
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    protected function _store(Request $request)
    {
        try {
        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'term_id' => 'required|integer|exists:user_terms,id',
            'agreed' => 'required|boolean',
            'agreed_at' => 'nullable|date',
                'version' => 'nullable|string|max:20',
                'consent_type' => 'nullable|in:initial,reconsent,withdrawal',
                'consent_method' => 'nullable|in:web,mobile,api,admin',
                'withdrawn_at' => 'nullable|date',
            'ip_address' => 'nullable|string|max:45',
                'user_agent' => 'nullable|string',
                'metadata' => 'nullable|array',
            ]);

            // 기본값 설정
            $validated['agreed_at'] = $validated['agreed_at'] ?? ($validated['agreed'] ? now() : null);
            $validated['consent_type'] = $validated['consent_type'] ?? 'initial';
            $validated['consent_method'] = $validated['consent_method'] ?? 'web';
            $validated['ip_address'] = $validated['ip_address'] ?? request()->ip();
            $validated['user_agent'] = $validated['user_agent'] ?? request()->userAgent();
            $validated['metadata'] = $validated['metadata'] ?? null;

            // 버전 정보가 없으면 약관의 현재 버전을 사용
            if (empty($validated['version'])) {
                $term = UserTerms::find($validated['term_id']);
                if ($term) {
                    $validated['version'] = $term->version;
                }
            }

        $log = UserTermLog::create($validated);
        return response()->json(['success' => true, 'message' => '로그가 등록되었습니다.', 'data' => $log], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => '입력 데이터가 올바르지 않습니다.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '로그 등록 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 로그 수정 폼
     *
     * 라우트: GET /terms/logs/{id}/edit
     * 기능: 로그 수정 폼을 표시
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\View\View
     */
    protected function _edit(Request $request, $id)
    {
        $item = UserTermLog::with(['user', 'term'])->findOrFail($id);
        return View::make($this->editPath, compact('item'));
    }

    /**
     * 로그 수정
     *
     * 라우트: PUT /terms/logs/{id}
     * 기능: 기존 로그를 수정
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    protected function _update(Request $request, $id)
    {
        try {
        $log = UserTermLog::findOrFail($id);
        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'term_id' => 'required|integer|exists:user_terms,id',
            'agreed' => 'required|boolean',
            'agreed_at' => 'nullable|date',
                'version' => 'nullable|string|max:20',
                'consent_type' => 'nullable|in:initial,reconsent,withdrawal',
                'consent_method' => 'nullable|in:web,mobile,api,admin',
                'withdrawn_at' => 'nullable|date',
            'ip_address' => 'nullable|string|max:45',
                'user_agent' => 'nullable|string',
                'metadata' => 'nullable|array',
            ]);

            // 동의 상태가 변경된 경우 agreed_at 업데이트
            if ($log->agreed !== $validated['agreed']) {
                $validated['agreed_at'] = $validated['agreed'] ? ($validated['agreed_at'] ?? now()) : null;
            }

            // 철회 상태가 변경된 경우 withdrawn_at 업데이트
            if (isset($validated['withdrawn_at']) && $validated['withdrawn_at'] !== $log->withdrawn_at) {
                if ($validated['withdrawn_at']) {
                    $validated['consent_type'] = 'withdrawal';
                    $validated['agreed'] = false;
                }
            }

        $log->update($validated);
        return response()->json(['success' => true, 'message' => '로그가 수정되었습니다.', 'data' => $log]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => '입력 데이터가 올바르지 않습니다.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '로그 수정 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 로그 삭제
     *
     * 라우트: DELETE /terms/logs/{id}
     * 기능: 로그를 삭제
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    protected function _destroy(Request $request)
    {
        try {
        $id = $request->get('id') ?? $request->route('id');
        if (!$id) {
            return response()->json(['success' => false, 'message' => '로그 ID가 제공되지 않았습니다.'], 400);
        }

        $log = UserTermLog::find($id);
        if (!$log) {
            return response()->json(['success' => false, 'message' => '해당 로그를 찾을 수 없습니다.'], 404);
        }

            // 삭제 전 데이터 가져오기 (Audit Log용)
            $oldData = $log->toArray();

        $log->delete();

            // Activity Log 기록
            $this->logActivity('delete', '삭제', $oldData, ['deleted_id' => $id]);

            // Audit Log 기록
            $this->logAudit('delete', $oldData, null, '약관 동의 로그 삭제', null);

            return response()->json([
                'success' => true,
                'message' => '로그가 삭제되었습니다.',
                'data' => [
                    'id' => $id,
                    'user_id' => $oldData['user_id'] ?? 'Unknown',
                    'term_id' => $oldData['term_id'] ?? 'Unknown'
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '삭제 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 로그 삭제 확인 폼
     *
     * 라우트: GET /terms/logs/{id}/delete-confirm
     * 기능: 로그 삭제 확인 폼을 표시
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function deleteConfirm(Request $request, $id)
    {
        $log = UserTermLog::with(['user', 'term'])->findOrFail($id);
        $url = route($this->getRouteName($request) . 'destroy', $id);
        $title = '약관 동의 로그 #' . $log->id . ' 삭제';
        $randomKey = strtoupper(substr(md5(uniqid()), 0, 8));

        // AJAX 요청인 경우 HTML만 반환
        if ($request->ajax()) {
            return view('jiny-auth::admin.terms-logs.form_delete', compact('log', 'url', 'title', 'randomKey'));
        }

        // $route 변수 추가
        $route = $this->getRouteName($request);

        // 일반 요청인 경우 전체 페이지 반환
        return view('jiny-auth::admin.terms-logs.form_delete', compact('log', 'url', 'title', 'randomKey', 'route'));
    }

    /**
     * 로그 일괄 삭제
     *
     * 라우트: POST /terms/logs/bulk-delete
     * 기능: 여러 로그를 일괄 삭제
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkDelete(Request $request)
    {
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:user_term_logs,id',
            ]);

            $ids = $request->ids;
            $count = count($ids);

            // 삭제 전 데이터 가져오기 (Audit Log용)
            $oldData = UserTermLog::whereIn('id', $ids)->get()->toArray();

            UserTermLog::whereIn('id', $ids)->delete();

            // Activity Log 기록
            $this->logActivity('delete', '일괄 삭제', null, ['deleted_ids' => $ids]);

            // Audit Log 기록
            $this->logAudit('delete', $oldData, null, '약관 동의 로그 일괄 삭제', null);

            return response()->json([
                'success' => true,
                'message' => "{$count}개의 로그가 성공적으로 삭제되었습니다.",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '일괄 삭제 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 동의 철회 처리
     *
     * 라우트: PATCH /terms/logs/{id}/withdraw
     * 기능: 사용자의 동의를 철회 처리
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function withdraw(Request $request, $id)
    {
        try {
            $log = UserTermLog::findOrFail($id);

            if (!$log->agreed) {
                return response()->json([
                    'success' => false,
                    'message' => '이미 동의하지 않은 로그입니다.'
                ], 400);
            }

            if ($log->withdrawn_at) {
                return response()->json([
                    'success' => false,
                    'message' => '이미 철회된 동의입니다.'
                ], 400);
            }

            $log->withdraw();

            return response()->json([
                'success' => true,
                'message' => '동의가 철회되었습니다.',
                'data' => $log
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '동의 철회 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 재동의 처리
     *
     * 라우트: PATCH /terms/logs/{id}/reconsent
     * 기능: 철회된 동의를 재동의 처리
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function reconsent(Request $request, $id)
    {
        try {
            $log = UserTermLog::findOrFail($id);

            if (!$log->withdrawn_at) {
                return response()->json([
                    'success' => false,
                    'message' => '철회되지 않은 동의입니다.'
                ], 400);
            }

            $log->reconsent();

            return response()->json([
                'success' => true,
                'message' => '재동의가 처리되었습니다.',
                'data' => $log
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '재동의 처리 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 사용자별 동의 이력 조회
     *
     * 라우트: GET /terms/logs/user-history
     * 기능: 특정 사용자의 동의 이력을 조회
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function userConsentHistory(Request $request)
    {
        try {
            $userId = $request->get('user_id');
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => '사용자 ID가 필요합니다.'
                ], 400);
            }

            $history = UserTermLog::where('user_id', $userId)
                                 ->with(['term'])
                                 ->orderBy('agreed_at', 'desc')
                                 ->get();

            return response()->json([
                'success' => true,
                'data' => $history
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '동의 이력 조회 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 약관별 동의 통계 조회
     *
     * 라우트: GET /terms/logs/term/{termId}/stats
     * 기능: 특정 약관의 동의 통계를 조회
     *
     * @param Request $request
     * @param int $termId
     * @return \Illuminate\Http\JsonResponse
     */
    public function termConsentStats(Request $request, $termId)
    {
        try {
            $stats = UserTermLog::where('term_id', $termId)
                               ->selectRaw('
                                   COUNT(*) as total,
                                   SUM(CASE WHEN agreed = 1 THEN 1 ELSE 0 END) as agreed_count,
                                   SUM(CASE WHEN agreed = 0 THEN 1 ELSE 0 END) as declined_count,
                                   SUM(CASE WHEN withdrawn_at IS NOT NULL THEN 1 ELSE 0 END) as withdrawn_count
                               ')
                               ->first();

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '통계 조회 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 삭제 전 데이터 조회 (Audit Log용)
     *
     * @param int $id
     * @return array|null
     */
    protected function getOldData($id)
    {
        $log = UserTermLog::find($id);
        return $log ? $log->toArray() : null;
    }
}
