<?php

namespace Jiny\Auth\App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Jiny\Auth\App\Services\AuthService;

/**
 * 로그아웃 액션
 * 
 * Single Action Controller
 * 로그아웃을 처리하는 단일 책임을 가진 컨트롤러
 */
class LogoutAction
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
     * 로그아웃 처리
     * 
     * @param Request $request
     * @return RedirectResponse
     */
    public function __invoke(Request $request): RedirectResponse
    {
        // 서비스를 통한 로그아웃 처리
        $this->authService->logout();

        // 로그인 페이지로 리다이렉트
        return redirect('/auth/login')->with('message', 'You have been logged out successfully.');
    }
}