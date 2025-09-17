<?php

namespace Jiny\Auth\App\Http\Controllers\Admin;

use Jiny\Admin\App\Http\Controllers\AdminResourceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use Jiny\Auth\App\Models\UserTerms;
use Jiny\Auth\App\Models\UserTermLog;
use Jiny\Auth\App\Services\TermVersionService;
use Illuminate\Support\Facades\Auth;

/**
 * AdminAuthTermsController
 *
 * 약관 관리 컨트롤러
 * 사용자 약관의 CRUD 작업과 버전 관리를 담당합니다.
 *
 * @package Jiny\Auth\App\Http\Controllers\Admin
 * @author JinyPHP
 * @version 1.0.0
 * @since 1.0.0
 * @license MIT
 *
 * @docs docs/features/AdminAuthTerms.md
 *
 * 🔄 기능 수정 시 테스트 실행 필요:
 * 이 컨트롤러의 기능이 수정되면 다음 테스트를 반드시 실행해주세요:
 *
 * ```bash
 * # 전체 약관 관리 테스트 실행
 * php artisan test jiny/auth/tests/Feature/AdminAuthTermsControllerTest.php
 * ```
 *
 * 📋 주요 테스트 항목:
 * 1. 약관 CRUD 작업 테스트
 * 2. 약관 버전 관리 및 이력 테스트
 * 3. 약관 활성화/비활성화 토글 테스트
 * 4. 약관 일괄 삭제 테스트
 * 5. 약관 통계 및 분석 테스트
 * 6. 약관 버전 비교 테스트
 * 7. 사용자 동의 이력 관리 테스트
 * 8. 필수 동의 약관 관리 테스트
 * 9. 슬러그 자동 생성 테스트
 * 10. 권한 검증 및 보안 테스트
 *
 * ⚠️ 주의사항:
 * - 약관 버전 관리 시 기존 버전 보존 확인
 * - 슬러그 중복 방지 및 자동 생성 확인
 * - 약관 활성화 시 기존 활성 약관 비활성화 확인
 * - 약관 삭제 시 관련 로그 함께 삭제 확인
 * - 약관 버전 비교 시 내용 차이점 표시 확인
 * - 사용자 동의 이력 무결성 확인
 *
 * 라우트 매핑:
 * - GET /terms -> index() - 약관 목록
 * - GET /terms/create -> create() - 약관 생성 폼
 * - POST /terms -> store() - 약관 저장
 * - GET /terms/statistics -> statistics() - 약관 통계
 * - POST /terms/bulk-delete -> bulkDelete() - 약관 일괄 삭제
 * - GET /terms/{id} -> show() - 약관 상세 보기
 * - GET /terms/{id}/edit -> edit() - 약관 수정 폼
 * - PUT /terms/{id} -> update() - 약관 수정
 * - DELETE /terms/{id} -> destroy() - 약관 삭제
 * - GET /terms/{id}/delete-confirm -> deleteConfirm() - 약관 삭제 확인
 * - PATCH /terms/{id}/toggle-active -> toggleActive() - 약관 활성화 토글
 * - POST /terms/{id}/versions/create -> createNewVersion() - 새 버전 생성
 * - PATCH /terms/{id}/versions/activate -> activateVersion() - 버전 활성화
 * - GET /terms/{id}/versions/history -> versionHistory() - 버전 이력
 * - GET /terms/{id}/consents/user-history -> userConsentHistory() - 사용자 동의 이력
 * - GET /terms/{id}/consents/required -> requiredConsents() - 필수 동의 목록
 * - POST /terms/compare -> compareVersions() - 약관 비교
 */
class AdminAuthTermsController extends AdminResourceController
{
    // 뷰 경로 변수 정의
    public $indexPath = 'jiny-auth::admin.terms.index';
    public $createPath = 'jiny-auth::admin.terms.create';
    public $editPath = 'jiny-auth::admin.terms.edit';
    public $showPath = 'jiny-auth::admin.terms.show';

    protected $filterable = ['title', 'type', 'is_active', 'version', 'manager_id'];
    protected $validFilters = ['title', 'type', 'is_active', 'version', 'manager_id', 'search'];
    protected $sortableColumns = ['title', 'type', 'is_active', 'version', 'display_order', 'effective_date', 'expiry_date', 'created_at', 'updated_at'];

    protected function getTableName() { return 'user_terms'; }
    protected function getModuleName() { return 'auth.user-terms'; }

    protected $termVersionService;

    public function __construct(TermVersionService $termVersionService)
    {
        $this->termVersionService = $termVersionService;
    }

    /**
     * 약관 목록 조회
     *
     * 라우트: GET /terms
     * 기능: 약관 목록을 페이지네이션과 함께 조회
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    protected function _index(Request $request)
    {
        $filters = $this->getFilterParameters($request);
        $perPage = $request->get('per_page', 15);
        $query = UserTerms::with(['manager', 'agreementLogs']);
        $likeFields = ['title', 'description', 'slug'];
        $query = $this->applyFilter($filters, $query, $likeFields);
        $query = $this->sort($query, $request);
        $terms = $query->paginate($perPage);

        return View::make($this->indexPath, [
            'rows' => $terms,
            'filters' => $filters,
            'stats' => $this->getStats()
        ]);
    }

    /**
     * 약관 통계 조회
     *
     * @return array
     */
    private function getStats()
    {
        return [
            'total' => UserTerms::count(),
            'active' => UserTerms::where('is_active', true)->count(),
            'required' => UserTerms::where('type', 'required')->count(),
            'optional' => UserTerms::where('type', 'optional')->count(),
            'total_agreements' => UserTermLog::count(),
            'expired' => UserTerms::where('expiry_date', '<', now())->count(),
            'pending_effective' => UserTerms::where('effective_date', '>', now())->count(),
        ];
    }

    /**
     * 약관 생성 폼
     *
     * 라우트: GET /terms/create
     * 기능: 약관 생성 폼을 표시
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    protected function _create(Request $request)
    {
        return View::make($this->createPath);
    }

    /**
     * 약관 저장
     *
     * 라우트: POST /terms
     * 기능: 새로운 약관을 생성하고 저장
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    protected function _store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'slug' => 'nullable|string|max:255|unique:user_terms,slug',
                'content' => 'required|string',
                'description' => 'nullable|string|max:1000',
                'type' => 'required|in:required,optional',
                'version' => 'nullable|string|max:20',
                'is_active' => 'nullable|boolean',
                'display_order' => 'nullable|integer|min:0',
                'effective_date' => 'nullable|date',
                'expiry_date' => 'nullable|date|after:effective_date',
                'manager_id' => 'nullable|integer|exists:users,id',
                'blade' => 'nullable|string|max:255',
                'metadata' => 'nullable|array',
            ]);

            // 인증된 사용자 확인
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => '인증되지 않은 사용자입니다. 다시 로그인해주세요.'
                ], 401);
            }

            $managerId = $validated['manager_id'] ?? Auth::id();

            // 기본값 설정
            $validated['manager_id'] = $managerId;
            $validated['version'] = $validated['version'] ?? '1.0';
            $validated['display_order'] = $validated['display_order'] ?? (UserTerms::max('display_order') + 1);
            $validated['is_active'] = isset($validated['is_active']) ? filter_var($validated['is_active'], FILTER_VALIDATE_BOOLEAN) : true;
            $validated['metadata'] = $validated['metadata'] ?? null;

            // 슬러그 자동 생성
            if (empty($validated['slug'])) {
                $validated['slug'] = UserTerms::generateSlug($validated['title']);
            }

            $term = UserTerms::create($validated);

            return response()->json([
                'success' => true,
                'message' => '약관이 등록되었습니다.',
                'data' => $term
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => '입력 데이터가 올바르지 않습니다.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '약관 등록 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 약관 상세 보기
     *
     * 라우트: GET /terms/{id}
     * 기능: 특정 약관의 상세 정보를 조회
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\View\View
     */
    protected function _show(Request $request, $id)
    {
        $item = UserTerms::with(['manager', 'agreementLogs.user'])->findOrFail($id);
        $logs = UserTermLog::where('term_id', $id)->with(['user'])->latest()->limit(10)->get();
        $versionHistory = $this->termVersionService->getVersionHistory($id);
        $consentStats = $this->termVersionService->getVersionConsentStats($id);

        return View::make($this->showPath, compact('item', 'logs', 'versionHistory', 'consentStats'));
    }

    /**
     * 약관 수정 폼
     *
     * 라우트: GET /terms/{id}/edit
     * 기능: 약관 수정 폼을 표시
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\View\View
     */
    protected function _edit(Request $request, $id)
    {
        $item = UserTerms::findOrFail($id);
        return View::make($this->editPath, compact('item'));
    }

    /**
     * 약관 수정
     *
     * 라우트: PUT /terms/{id}
     * 기능: 기존 약관을 수정
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    protected function _update(Request $request, $id)
    {
        try {
            $term = UserTerms::findOrFail($id);

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'slug' => 'nullable|string|max:255|unique:user_terms,slug,' . $id,
                'content' => 'required|string',
                'description' => 'nullable|string|max:1000',
                'type' => 'required|in:required,optional',
                'version' => 'nullable|string|max:20',
                'is_active' => 'nullable|boolean',
                'display_order' => 'nullable|integer|min:0',
                'effective_date' => 'nullable|date',
                'expiry_date' => 'nullable|date|after:effective_date',
                'manager_id' => 'nullable|integer|exists:users,id',
                'blade' => 'nullable|string|max:255',
                'metadata' => 'nullable|array',
            ]);

            // boolean 필드 처리
            if (isset($validated['is_active'])) {
                $validated['is_active'] = filter_var($validated['is_active'], FILTER_VALIDATE_BOOLEAN);
            }

            // 슬러그 자동 생성 (제목이 변경된 경우)
            if (empty($validated['slug']) && $term->title !== $validated['title']) {
                $validated['slug'] = UserTerms::generateSlug($validated['title']);
            }

            $term->update($validated);

            return response()->json([
                'success' => true,
                'message' => '약관이 수정되었습니다.',
                'data' => $term
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => '입력 데이터가 올바르지 않습니다.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '약관 수정 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 약관 삭제
     *
     * 라우트: DELETE /terms/{id}
     * 기능: 약관을 삭제하고 관련 로그도 함께 삭제
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    protected function _destroy(Request $request)
    {
        try {
            $id = $request->get('id') ?? $request->route('id');
            if (!$id) {
                return response()->json(['success' => false, 'message' => '약관 ID가 제공되지 않았습니다.'], 400);
            }

            $term = UserTerms::find($id);
            if (!$term) {
                return response()->json(['success' => false, 'message' => '해당 약관을 찾을 수 없습니다.'], 404);
            }

            // 삭제 전 데이터 가져오기 (Audit Log용)
            $oldData = $term->toArray();

            // 관련 로그도 함께 삭제
            UserTermLog::where('term_id', $id)->delete();

            $term->delete();

            // Activity Log 기록
            $this->logActivity('delete', '삭제', $oldData, ['deleted_id' => $id]);

            // Audit Log 기록
            $this->logAudit('delete', $oldData, null, '약관 삭제', null);

            return response()->json([
                'success' => true,
                'message' => '약관이 삭제되었습니다.',
                'data' => [
                    'id' => $id,
                    'title' => $oldData['title'] ?? 'Unknown'
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
     * 약관 삭제 확인 폼
     *
     * 라우트: GET /terms/{id}/delete-confirm
     * 기능: 약관 삭제 확인 폼을 표시
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function deleteConfirm(Request $request, $id)
    {
        $term = UserTerms::findOrFail($id);
        $url = route($this->getRouteName($request) . 'destroy', $id);
        $title = $term->title . ' 삭제';
        $randomKey = strtoupper(substr(md5(uniqid()), 0, 8));

        // AJAX 요청인 경우 HTML만 반환
        if ($request->ajax()) {
            return view('jiny-auth::admin.terms.form_delete', compact('term', 'url', 'title', 'randomKey'));
        }

        // $route 변수 추가
        $route = $this->getRouteName($request);

        // 일반 요청인 경우 전체 페이지 반환
        return view('jiny-auth::admin.terms.form_delete', compact('term', 'url', 'title', 'randomKey', 'route'));
    }

    /**
     * 약관 활성화 토글
     *
     * 라우트: PATCH /terms/{id}/toggle-active
     * 기능: 약관의 활성화 상태를 토글
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleActive(Request $request, $id)
    {
        $term = UserTerms::findOrFail($id);
        $term->is_active = !$term->is_active;
        $term->save();
        return response()->json(['success' => true, 'is_active' => $term->is_active]);
    }

    /**
     * 약관 일괄 삭제
     *
     * 라우트: POST /terms/bulk-delete
     * 기능: 여러 약관을 일괄 삭제
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkDelete(Request $request)
    {
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:user_terms,id',
            ]);

            $ids = $request->ids;
            $count = count($ids);

            // 삭제 전 데이터 가져오기 (Audit Log용)
            $oldData = UserTerms::whereIn('id', $ids)->get()->toArray();

            // 관련 로그도 함께 삭제
            UserTermLog::whereIn('term_id', $ids)->delete();

            UserTerms::whereIn('id', $ids)->delete();

            // Activity Log 기록
            $this->logActivity('delete', '일괄 삭제', null, ['deleted_ids' => $ids]);

            // Audit Log 기록
            $this->logAudit('delete', $oldData, null, '약관 일괄 삭제', null);

            return response()->json([
                'success' => true,
                'message' => "{$count}개의 약관이 성공적으로 삭제되었습니다.",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '일괄 삭제 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 약관 통계 조회
     *
     * 라우트: GET /terms/statistics
     * 기능: 약관 관련 통계 정보를 조회
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function statistics(Request $request)
    {
        $stats = $this->getStats();
        return response()->json(['success' => true, 'data' => $stats]);
    }

    /**
     * 새 버전의 약관 생성
     *
     * 라우트: POST /terms/{id}/versions/create
     * 기능: 기존 약관의 새 버전을 생성
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function createNewVersion(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'description' => 'nullable|string|max:1000',
                'deactivate_previous' => 'nullable|boolean',
            ]);

            $newVersion = $this->termVersionService->createNewVersion($id, $validated);

            return response()->json([
                'success' => true,
                'message' => '새 버전의 약관이 생성되었습니다.',
                'data' => $newVersion
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '새 버전 생성 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 약관 버전 활성화
     *
     * 라우트: PATCH /terms/{id}/versions/activate
     * 기능: 특정 약관 버전을 활성화
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function activateVersion(Request $request, $id)
    {
        try {
            $term = $this->termVersionService->activateVersion($id);

            return response()->json([
                'success' => true,
                'message' => '약관 버전이 활성화되었습니다.',
                'data' => $term
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '버전 활성화 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 약관 버전 이력 조회
     *
     * 라우트: GET /terms/{id}/versions/history
     * 기능: 약관의 버전 이력을 조회
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function versionHistory(Request $request, $id)
    {
        try {
            $history = $this->termVersionService->getVersionHistory($id);
            $stats = $this->termVersionService->getVersionConsentStats($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'history' => $history,
                    'stats' => $stats
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '버전 이력 조회 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 사용자 동의 이력 조회
     *
     * 라우트: GET /terms/{id}/consents/user-history
     * 기능: 특정 약관에 대한 사용자 동의 이력을 조회
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function userConsentHistory(Request $request, $id)
    {
        try {
            $userId = $request->get('user_id');
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => '사용자 ID가 필요합니다.'
                ], 400);
            }

            $history = $this->termVersionService->getUserConsentHistory($userId, $id);

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
     * 약관 버전 비교
     *
     * 라우트: POST /terms/compare
     * 기능: 두 약관 버전을 비교
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function compareVersions(Request $request)
    {
        try {
            $validated = $request->validate([
                'term_id_1' => 'required|exists:user_terms,id',
                'term_id_2' => 'required|exists:user_terms,id',
            ]);

            $comparison = $this->termVersionService->compareVersions(
                $validated['term_id_1'],
                $validated['term_id_2']
            );

            return response()->json([
                'success' => true,
                'data' => $comparison
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '버전 비교 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 사용자가 동의해야 하는 약관 목록 조회
     *
     * 라우트: GET /terms/{id}/consents/required
     * 기능: 사용자가 동의해야 하는 약관 목록을 조회
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function requiredConsents(Request $request)
    {
        try {
            $userId = $request->get('user_id');
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => '사용자 ID가 필요합니다.'
                ], 400);
            }

            $requiredConsents = $this->termVersionService->getRequiredConsentsForUser($userId);

            return response()->json([
                'success' => true,
                'data' => $requiredConsents
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '필수 동의 목록 조회 중 오류가 발생했습니다: ' . $e->getMessage()
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
        $term = UserTerms::find($id);
        return $term ? $term->toArray() : null;
    }
}
