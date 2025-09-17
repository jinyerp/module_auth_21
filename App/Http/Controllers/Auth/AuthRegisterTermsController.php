<?php

namespace Jiny\Auth\App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Jiny\Auth\App\Models\UserTerms;
use Jiny\Auth\App\Models\UserTermLog;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AuthRegisterTermsController extends Controller
{
    protected $config;

    public function __construct()
    {
        $this->config = config('admin.auth');
    }

    /**
     * 약관 동의 페이지 표시
     */
    public function index()
    {
        // 1단계: 시스템 약관 기능 확인
        $systemCheck = $this->checkTermsSystemAvailability();
        if (!$systemCheck['success']) {
            return $systemCheck['response'];
        }

        // 2단계: 약관 데이터 준비
        $termsData = $this->prepareTermsData();
        if (!$termsData['success']) {
            return $termsData['response'];
        }

        // 3단계: 약관 동의 페이지 표시
        return $this->displayTermsPage($termsData['data']);
    }

    /**
     * 약관 상세 내용 표시 (모달용)
     */
    public function show($id)
    {
        $term = UserTerms::findOrFail($id);
        return response()->json([
            'title' => $term->title,
            'content' => $term->content,
            'type' => $term->type
        ]);
    }

    /**
     * 약관 동의 처리
     */
    public function store(Request $request)
    {
        // 1단계: 시스템 약관 기능 확인
        $systemCheck = $this->checkTermsSystemAvailability();
        if (!$systemCheck['success']) {
            return response()->json([
                'status' => 'error',
                'message' => '약관 기능이 비활성화되었습니다.'
            ], 403);
        }

        // 2단계: 입력 데이터 검증
        $validationCheck = $this->validateTermsInput($request);
        if (!$validationCheck['success']) {
            return $validationCheck['response'];
        }

        // 3단계: 약관 동의 검증
        $agreementCheck = $this->validateTermsAgreement($request);
        if (!$agreementCheck['success']) {
            return $agreementCheck['response'];
        }

        // 4단계: 약관 동의 로그 생성
        $logsCreation = $this->createTermsAgreementLogs($request);
        if (!$logsCreation['success']) {
            return $logsCreation['response'];
        }

        // 5단계: 세션에 동의 정보 저장
        $this->saveTermsAgreementToSession($request, $logsCreation['logs']);

        // 6단계: 성공 응답 반환
        return $this->returnTermsAgreementSuccess();
    }

    /**
     * 약관 동의 확인
     */
    public function check()
    {
        // 약관 기능이 비활성화된 경우
        if (!$this->config['terms']['enabled']) {
            return response()->json([
                'agreed' => false,
                'message' => '약관 기능이 비활성화되었습니다.'
            ]);
        }

        $agreedTerms = session('terms_agreed', []);
        $requiredTerms = UserTerms::where('type', 'required')
            ->where('is_active', true)
            ->pluck('id');

        $allRequiredAgreed = $requiredTerms->every(function ($termId) use ($agreedTerms) {
            return in_array($termId, $agreedTerms);
        });

        return response()->json([
            'agreed' => $allRequiredAgreed,
            'agreed_terms' => $agreedTerms,
            'required_terms' => $requiredTerms
        ]);
    }

    /**
     * 약관 동의 초기화
     */
    public function reset()
    {
        // 1단계: 시스템 약관 기능 확인
        $systemCheck = $this->checkTermsSystemAvailability();
        if (!$systemCheck['success']) {
            return $systemCheck['response'];
        }

        // 2단계: 세션 초기화
        $this->clearTermsAgreementSession();

        // 3단계: 초기화 완료 리다이렉트
        return redirect()
            ->route('regist.terms')
            ->with('info', '약관 동의가 초기화되었습니다.');
    }

    // ========================================
    // 1단계: 시스템 약관 기능 확인
    // ========================================

    /**
     * 약관 시스템 가용성 확인
     */
    private function checkTermsSystemAvailability(): array
    {
        // 약관 기능이 비활성화된 경우
        if (!$this->config['terms']['enabled']) {
            // 현재 페이지가 이미 회원가입 페이지인 경우 무한 리다이렉션 방지
            if (request()->routeIs('regist')) {
                return ['success' => true];
            }
            
            return [
                'success' => false,
                'response' => redirect()->route('regist')->with('error', '약관 기능이 비활성화되었습니다.')
            ];
        }

        return ['success' => true];
    }

    // ========================================
    // 2단계: 약관 데이터 준비
    // ========================================

    /**
     * 약관 데이터 준비
     */
    private function prepareTermsData(): array
    {
        try {
            // 활성화된 약관들을 표시 순서대로 조회
            $terms = UserTerms::where('is_active', true)
                ->orderBy('sort_order', 'asc')
                ->get();

            // 약관이 없는 경우 처리
            if ($terms->isEmpty()) {
                return [
                    'success' => true,
                    'data' => [
                        'requiredTerms' => collect(),
                        'optionalTerms' => collect(),
                        'hasTerms' => false
                    ]
                ];
            }

            // 필수 약관과 선택 약관 분리
            $requiredTerms = $terms->where('type', 'required');
            $optionalTerms = $terms->where('type', 'optional');

            return [
                'success' => true,
                'data' => [
                    'requiredTerms' => $requiredTerms,
                    'optionalTerms' => $optionalTerms,
                    'hasTerms' => true
                ]
            ];
        } catch (\Exception $e) {
            Log::error('약관 데이터 준비 실패', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'response' => redirect()
                    ->route('regist')
                    ->with('error', '약관 정보를 불러오는 중 오류가 발생했습니다.')
            ];
        }
    }

    // ========================================
    // 3단계: 약관 동의 페이지 표시
    // ========================================

    /**
     * 약관 동의 페이지 표시
     */
    private function displayTermsPage(array $termsData)
    {
        return view('jiny-auth::auth.regist_terms', $termsData);
    }

    // ========================================
    // 약관 동의 처리 단계별 메서드
    // ========================================

    /**
     * 약관 입력 데이터 검증
     */
    private function validateTermsInput(Request $request): array
    {
        try {
            $request->validate([
                'agreed_terms' => 'required|array',
                'agreed_terms.*' => 'integer|exists:user_terms,id'
            ]);

            return ['success' => true];
        } catch (\Illuminate\Validation\ValidationException $e) {
            return [
                'success' => false,
                'response' => response()->json([
                    'status' => 'error',
                    'message' => '약관 동의 정보가 올바르지 않습니다.',
                    'errors' => $e->errors()
                ], 422)
            ];
        }
    }

    /**
     * 약관 동의 검증
     */
    private function validateTermsAgreement(Request $request): array
    {
        // 활성화된 약관 목록 조회
        $terms = UserTerms::where('is_active', true)->get();

        if ($terms->isEmpty()) {
            // 약관이 없는 경우 통과
            return ['success' => true];
        }

        // 필수 약관 목록
        $requiredTerms = $terms->where('type', 'required')->pluck('id');
        $agreedTerms = collect($request->agreed_terms);
        $missingRequired = $requiredTerms->diff($agreedTerms);

        if ($missingRequired->count() > 0) {
            return [
                'success' => false,
                'response' => response()->json([
                    'status' => 'error',
                    'message' => '필수 약관에 모두 동의해주세요.',
                    'missing_terms' => $missingRequired->toArray()
                ], 422)
            ];
        }

        return ['success' => true];
    }

    /**
     * 약관 동의 로그 생성
     */
    private function createTermsAgreementLogs(Request $request): array
    {
        try {
            // 활성화된 약관 목록 조회
            $terms = UserTerms::where('is_active', true)->get();

            if ($terms->isEmpty()) {
                // 약관이 없는 경우 자동 승인 로그
                $logs = [[
                    'term_id' => null,
                    'term_title' => '자동승인',
                    'term_type' => 'required',
                    'user_id' => null,
                    'email' => null,
                    'name' => null,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'agreed_at' => now()
                ]];
            } else {
                // 약관 동의 로그 생성
                $logs = [];
                foreach ($request->agreed_terms as $termId) {
                    $term = UserTerms::find($termId);
                    if ($term) {
                        $logs[] = [
                            'term_id' => $termId,
                            'term_title' => $term->title,
                            'term_type' => $term->type,
                            'user_id' => null,
                            'email' => null,
                            'name' => null,
                            'ip_address' => $request->ip(),
                            'user_agent' => $request->userAgent(),
                            'agreed_at' => now()
                        ];
                    }
                }
            }

            return ['success' => true, 'logs' => $logs];
        } catch (\Exception $e) {
            Log::error('약관 동의 로그 생성 실패', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'response' => response()->json([
                    'status' => 'error',
                    'message' => '약관 동의 처리 중 오류가 발생했습니다.'
                ], 500)
            ];
        }
    }

    /**
     * 세션에 약관 동의 정보 저장
     */
    private function saveTermsAgreementToSession(Request $request, array $logs): void
    {
        session(['terms_agreement_logs' => $logs]);
        session(['terms_agreed' => $request->agreed_terms]);
    }

    /**
     * 약관 동의 성공 응답 반환
     */
    private function returnTermsAgreementSuccess()
    {
        return response()->json([
            'status' => 'success',
            'message' => '약관 동의가 완료되었습니다. 회원가입을 진행해주세요.',
            'redirect' => route('regist')
        ]);
    }

    /**
     * 약관 동의 세션 초기화
     */
    private function clearTermsAgreementSession(): void
    {
        session()->forget(['terms_agreement_logs', 'terms_agreed']);
    }
}
