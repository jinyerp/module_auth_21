<?php

namespace Jiny\Auth\App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * 로그인 페이지 표시 액션
 * 
 * Single Action Controller
 * 로그인 페이지를 표시하는 단일 책임을 가진 컨트롤러
 */
class ShowLoginPageAction
{
    /**
     * 로그인 페이지 표시
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        // 실제 구현에서는 view를 반환
        // 현재는 TDD를 위해 JSON 응답
        return response()->json([
            'page' => 'login',
            'title' => 'Login',
            'message' => 'Please enter your credentials'
        ]);
    }
}