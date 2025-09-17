<?php

use Illuminate\Support\Facades\Route;
use Jiny\Auth\App\Http\Controllers\Api\ApiAuthController;

/**
 * API 인증 관련 라우트
 * Prefix: /api/auth
 * Middleware: ['api']
 */

Route::prefix('api/auth')->middleware(['api'])->group(function () {
    
    // 인증 (로그인/로그아웃)
    Route::post('/login', [ApiAuthController::class, 'login'])->name('api.auth.login');
    Route::post('/logout', [ApiAuthController::class, 'logout'])->name('api.auth.logout')->middleware('auth:sanctum');
    Route::post('/refresh', [ApiAuthController::class, 'refresh'])->name('api.auth.refresh')->middleware('auth:sanctum');
    
    // 회원가입
    Route::post('/register', [ApiAuthController::class, 'register'])->name('api.auth.register');
    
    // 인증된 사용자 전용 API
    Route::middleware(['auth:sanctum'])->group(function () {
        
        // 사용자 정보
        Route::get('/user', [ApiAuthController::class, 'user'])->name('api.auth.user');
        Route::post('/logout-all', [ApiAuthController::class, 'logoutAll'])->name('api.auth.logout.all');
        Route::get('/tokens', [ApiAuthController::class, 'tokens'])->name('api.auth.tokens');
        Route::delete('/tokens/{id}', [ApiAuthController::class, 'revokeToken'])->name('api.auth.tokens.revoke');
    });
});

// API 사용자 정보 (Sanctum 인증 필요)
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/api/user', [ApiAuthController::class, 'user'])->name('api.user');
    
    // API Extended Routes from auth-api module
    Route::get('/api/profile', [ApiAuthController::class, 'profile'])->name('api.profile');
    Route::put('/api/profile', [ApiAuthController::class, 'updateProfile'])->name('api.profile.update');
});