<?php

namespace Jiny\Auth\App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Jiny\Auth\App\Services\AuthService;

/**
 * 로그인 처리 액션
 * 
 * Single Action Controller
 * 로그인 요청을 처리하는 단일 책임을 가진 컨트롤러
 */
class ProcessLoginAction
{
    /**
     * @var AuthService
     */
    private AuthService $authService;

    /**
     * 생성자
     * 
     * @param AuthService $authService
     */
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * 로그인 처리
     * 
     * @param Request $request
     * @return RedirectResponse
     */
    public function __invoke(Request $request): RedirectResponse
    {
        // 입력 검증
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // 서비스를 통한 로그인 처리
        $result = $this->authService->login($credentials);

        if (!$result['success']) {
            return back()->withErrors([
                $result['field'] ?? 'email' => $result['message']
            ])->withInput($request->except('password'));
        }

        // 성공 시 리다이렉트
        return redirect()->intended($result['redirect']);
    }
}