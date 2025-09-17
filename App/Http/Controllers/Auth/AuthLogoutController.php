<?php

namespace Jiny\Auth\App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Jiny\Auth\App\Models\Account;
use Carbon\Carbon;

/**
 * AuthLogoutController
 *
 * 로그아웃 처리를 담당하는 컨트롤러
 *
 * @package Jiny\Auth\App\Http\Controllers\Auth
 * @author JinyPHP
 * @version 1.0.0
 * @since 1.0.0
 * @license MIT
 *
 * 🔄 관련 테스트:
 * - test_logout_process
 * - test_ajax_logout_process
 */
class AuthLogoutController extends Controller
{
    private $config;

    /**
     * 생성자
     */
    public function __construct()
    {
        $this->config = config('jiny-auth', [
            'auth' => ['logout' => ['redirect_route' => 'login']],
            'login' => ['enabled' => true]
        ]);
    }

    /**
     * 실제 로그아웃 처리 로직 (공통)
     */
    private function performLogout(Request $request, $user = null)
    {
        if ($user) {
            $this->logLogout($user);
        }
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    /**
     * GET 로그아웃 처리
     */
    public function index(Request $request)
    {
        return $this->logout($request);
    }

    /**
     * POST 로그아웃 처리 메서드
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        // 로그아웃 기능이 비활성화된 경우
        if (!$this->isLogoutEnabled()) {
            return redirect()->route('login')->with('error', '로그아웃 기능이 비활성화되었습니다.');
        }

        $user = Auth::user();
        $this->performLogout($request, $user);

        // 로그아웃 후 리다이렉트 설정
        $redirectRoute = $this->config['auth']['logout']['redirect_route'] ?? 'login';
        return redirect()->route($redirectRoute)->with('success', '로그아웃이 완료되었습니다.');
    }

    /**
     * Ajax 로그아웃 처리 메서드
     *
     * 승인 페이지에서 AJAX 요청으로 로그아웃을 처리합니다.
     * JSON 응답을 반환하고 클라이언트에서 리다이렉션을 처리합니다.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajaxSessionLogout(Request $request)
    {
        try {
            // 1. AJAX 요청 확인
            if (!$request->ajax() && !$request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'AJAX 요청만 허용됩니다.'
                ], 400);
            }

            // 2. 로그아웃 기능이 비활성화된 경우
            if (!$this->isLogoutEnabled()) {
                return response()->json([
                    'success' => false,
                    'message' => '로그아웃 기능이 비활성화되었습니다.'
                ], 403);
            }

            // 3. 현재 사용자 정보 가져오기
            $user = Auth::user();

            // 4. 로그아웃 처리
            $this->performLogout($request, $user);

            // 5. 로그아웃 후 리다이렉트 설정
            $redirectRoute = $this->config['auth']['logout']['redirect_route'] ?? 'login';
            $redirectUrl = route($redirectRoute);

            // 6. 성공 응답 반환
            return response()->json([
                'success' => true,
                'message' => '로그아웃이 완료되었습니다.',
                'redirect' => $redirectUrl
            ], 200, [], JSON_UNESCAPED_UNICODE);

        } catch (\Exception $e) {
            Log::error('Ajax 로그아웃 실패', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => false,
                'message' => '로그아웃 처리 중 오류가 발생했습니다.'
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * 로그아웃 기능이 활성화되어 있는지 확인
     */
    private function isLogoutEnabled(): bool
    {
        $logoutEnabled = $this->config['login']['enabled'] ?? true;

        // 문자열로 저장된 경우 boolean으로 변환
        if (is_string($logoutEnabled)) {
            $logoutEnabled = filter_var($logoutEnabled, FILTER_VALIDATE_BOOLEAN);
        }

        return $logoutEnabled;
    }

    /**
     * 로그아웃 로그 기록
     */
    protected function logLogout($user)
    {
        Log::info('로그아웃', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
    }
}
