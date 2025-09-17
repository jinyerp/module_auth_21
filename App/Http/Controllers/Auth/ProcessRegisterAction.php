<?php

namespace Jiny\Auth\App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Jiny\Auth\App\Services\AuthService;

/**
 * 회원가입 처리 액션
 * 
 * Single Action Controller
 * 회원가입 요청을 처리하는 단일 책임을 가진 컨트롤러
 */
class ProcessRegisterAction
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
     * 회원가입 처리
     * 
     * @param Request $request
     * @return RedirectResponse
     */
    public function __invoke(Request $request): RedirectResponse
    {
        // 입력 검증
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // 서비스를 통한 회원가입 처리
        $result = $this->authService->register($data);

        if (!$result['success']) {
            return back()->withErrors([
                'email' => $result['message']
            ])->withInput($request->except('password', 'password_confirmation'));
        }

        // 성공 시 리다이렉트
        return redirect($result['redirect']);
    }
}