<?php

use Illuminate\Support\Facades\Route;
use Jiny\Auth\App\Http\Controllers\Auth\LoginController;
use Jiny\Auth\App\Http\Controllers\Auth\RegisterController;
use Jiny\Auth\App\Http\Controllers\Auth\ForgotPasswordController;
use Jiny\Auth\App\Http\Controllers\Auth\ResetPasswordController;
use Jiny\Auth\App\Http\Controllers\Auth\VerificationController;
use Jiny\Auth\App\Http\Controllers\Auth\ConfirmPasswordController;
use Jiny\Auth\App\Http\Controllers\Auth\TwoFactorController;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
|
| 인증 관련 모든 라우트
|
*/

Route::middleware(['web'])->group(function () {
    
    // 비인증 사용자만 접근 가능
    Route::middleware(['guest'])->group(function () {
        
        // 로그인
        Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
        Route::post('login', [LoginController::class, 'login']);
        
        // 회원가입
        Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
        Route::post('register', [RegisterController::class, 'register']);
        
        // 비밀번호 재설정
        Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
        Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
        Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
        Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');
    });
    
    // 인증된 사용자만 접근 가능
    Route::middleware(['auth'])->group(function () {
        
        // 로그아웃
        Route::post('logout', [LoginController::class, 'logout'])->name('logout');
        Route::get('logout', [LoginController::class, 'logout']); // GET 메서드도 지원
        
        // 이메일 인증
        Route::get('email/verify', [VerificationController::class, 'show'])->name('verification.notice');
        Route::get('email/verify/{id}/{hash}', [VerificationController::class, 'verify'])->name('verification.verify');
        Route::post('email/resend', [VerificationController::class, 'resend'])->name('verification.resend');
        
        // 비밀번호 확인
        Route::get('password/confirm', [ConfirmPasswordController::class, 'showConfirmForm'])->name('password.confirm');
        Route::post('password/confirm', [ConfirmPasswordController::class, 'confirm']);
        
        // 2FA 인증
        Route::prefix('two-factor')->name('two-factor.')->group(function () {
            Route::get('challenge', [TwoFactorController::class, 'showChallenge'])->name('challenge');
            Route::post('challenge', [TwoFactorController::class, 'verify'])->name('verify');
            Route::post('recovery', [TwoFactorController::class, 'recovery'])->name('recovery');
        });
        
        // 비밀번호 만료 체크
        Route::get('password/expired', [ResetPasswordController::class, 'showExpiredForm'])->name('password.expired');
        Route::post('password/expired', [ResetPasswordController::class, 'updateExpired'])->name('password.expired.update');
    });
    
    // 소셜 로그인 (OAuth)
    Route::prefix('auth')->name('auth.')->group(function () {
        Route::get('{provider}', [LoginController::class, 'redirectToProvider'])->name('provider');
        Route::get('{provider}/callback', [LoginController::class, 'handleProviderCallback'])->name('callback');
    });
});