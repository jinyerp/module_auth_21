<?php

use Illuminate\Support\Facades\Route;
use Jiny\Auth\App\Http\Controllers\Auth\AuthLoginController;
use Jiny\Auth\App\Http\Controllers\Auth\AuthLogoutController;
use Jiny\Auth\App\Http\Controllers\Auth\AuthRegisterController;
use Jiny\Auth\App\Http\Controllers\Auth\AuthRegistStoreController;
use Jiny\Auth\App\Http\Controllers\Auth\AuthRegisterTermsController;
use Jiny\Auth\App\Http\Controllers\Auth\AuthApprovalController;
use Jiny\Auth\App\Http\Controllers\Jwt\AuthJwtSigninController;
use Jiny\Auth\App\Http\Controllers\Jwt\AuthJwtSignupController;
use Jiny\Auth\App\Http\Controllers\Jwt\AuthJwtSignoutController;
use Jiny\Auth\App\Http\Controllers\Auth\PasswordResetController;
use Jiny\Auth\App\Http\Controllers\Auth\EmailVerificationController;
use Jiny\Auth\App\Http\Controllers\Home\HomeController;
use Jiny\Auth\App\Http\Controllers\Home\PasswordController;

/**
 * 일반 사용자 인증 관련 라우트
 * Middleware: ['web']
 */

// 로그인/로그아웃
Route::middleware(['web'])->group(function () {
    Route::get('/login', [AuthLoginController::class, 'index'])->name('login');
    Route::post('/login', [AuthLoginController::class, 'login'])->name('login.post');
    Route::post('/logout', [AuthLogoutController::class, 'logout'])->name('logout')->middleware('auth');
    Route::get('/logout', [AuthLogoutController::class, 'index'])->name('logout.get')->middleware('auth');
});

// 회원가입
Route::middleware(['web'])->group(function () {
    Route::get('/register', [AuthRegisterController::class, 'index'])->name('register');
    Route::post('/register', [AuthRegistStoreController::class, 'store'])->name('register.post');
    Route::get('/register/terms', [AuthRegisterTermsController::class, 'index'])->name('register.terms');
    Route::post('/register/terms', [AuthRegisterTermsController::class, 'agree'])->name('register.terms.post');
    
    // 승인 시스템
    Route::get('/register/approval', [\Jiny\Auth\App\Http\Controllers\Auth\ApprovalController::class, 'index'])->name('register.approval');
    Route::post('/register/approval/check', [\Jiny\Auth\App\Http\Controllers\Auth\ApprovalController::class, 'check'])->name('register.approval.check');
    Route::post('/register/approval/resend', [\Jiny\Auth\App\Http\Controllers\Auth\ApprovalController::class, 'resend'])->name('register.approval.resend');
});

// 비밀번호 재설정
Route::get('/forgot-password', [PasswordResetController::class, 'showForgotForm'])->name('password.request');
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.update');

// 이메일 인증
Route::get('/email/verify', [EmailVerificationController::class, 'notice'])->name('verification.notice')->middleware('auth');
Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])->name('verification.verify')->middleware(['auth', 'signed']);
Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])->name('verification.send')->middleware(['auth', 'throttle:6,1']);

// 2단계 인증 (2FA) - 로그인 시
Route::middleware(['web'])->group(function () {
    Route::get('/login/2fa', [\Jiny\Auth\App\Http\Controllers\Auth\Login2FAController::class, 'index'])->name('login.2fa');
    Route::post('/login/2fa/verify', [\Jiny\Auth\App\Http\Controllers\Auth\Login2FAController::class, 'verify'])->name('login.2fa.verify');
    Route::get('/login/2fa/cancel', [\Jiny\Auth\App\Http\Controllers\Auth\Login2FAController::class, 'cancel'])->name('login.2fa.cancel');
});

// 휴면계정 처리
Route::middleware(['web'])->group(function () {
    Route::get('/login/dormant', [\Jiny\Auth\App\Http\Controllers\Auth\DormantController::class, 'index'])->name('dormant.index');
    Route::post('/login/dormant/activate', [\Jiny\Auth\App\Http\Controllers\Auth\DormantController::class, 'requestActivation'])->name('dormant.request-activation');
    Route::get('/login/dormant/activate/{token}', [\Jiny\Auth\App\Http\Controllers\Auth\DormantController::class, 'activate'])->name('dormant.activate');
});

// 2단계 인증 (2FA) - 사용자 설정
Route::middleware(['auth'])->prefix('2fa')->group(function () {
    Route::get('/setup', [\Jiny\Auth\App\Http\Controllers\Auth\TwoFactorController::class, 'setup'])->name('2fa.setup');
    Route::post('/enable', [\Jiny\Auth\App\Http\Controllers\Auth\TwoFactorController::class, 'enable'])->name('2fa.enable');
    Route::get('/challenge', [\Jiny\Auth\App\Http\Controllers\Auth\TwoFactorController::class, 'challenge'])->name('2fa.challenge');
    Route::post('/verify', [\Jiny\Auth\App\Http\Controllers\Auth\TwoFactorController::class, 'verify'])->name('2fa.verify');
    Route::post('/disable', [\Jiny\Auth\App\Http\Controllers\Auth\TwoFactorController::class, 'disable'])->name('2fa.disable');
    Route::post('/recovery-codes', [\Jiny\Auth\App\Http\Controllers\Auth\TwoFactorController::class, 'regenerateRecoveryCodes'])->name('2fa.recovery-codes');
});

// 2FA 백업 코드 (계정 설정 내)
Route::middleware(['auth'])->prefix('home/account/2fa')->group(function () {
    Route::get('/backup-codes', [\Jiny\Auth\App\Http\Controllers\Auth\TwoFactorController::class, 'backupCodes'])->name('account.2fa.backup-codes');
});

// 사용자 홈 (인증 필요)
Route::middleware(['auth'])->prefix('home')->group(function () {
    // 대시보드
    Route::get('/', [HomeController::class, 'index'])->name('home');
    
    // 프로필 관리 (새로운 ProfileController 사용)
    Route::prefix('profile')->group(function () {
        Route::get('/', [\Jiny\Auth\App\Http\Controllers\Home\ProfileController::class, 'index'])->name('home.profile');
        Route::get('/edit', [\Jiny\Auth\App\Http\Controllers\Home\ProfileController::class, 'edit'])->name('home.profile.edit');
        Route::put('/', [\Jiny\Auth\App\Http\Controllers\Home\ProfileController::class, 'update'])->name('home.profile.update');
        Route::get('/avatar', [\Jiny\Auth\App\Http\Controllers\Home\ProfileController::class, 'avatar'])->name('home.profile.avatar');
        Route::post('/avatar', [\Jiny\Auth\App\Http\Controllers\Home\ProfileController::class, 'updateAvatar'])->name('home.profile.avatar.update');
        Route::get('/avatar/history', [\Jiny\Auth\App\Http\Controllers\Home\ProfileController::class, 'avatarHistory'])->name('home.profile.avatar.history');
        Route::get('/addresses', [\Jiny\Auth\App\Http\Controllers\Home\ProfileController::class, 'addresses'])->name('home.profile.addresses');
        Route::post('/addresses', [\Jiny\Auth\App\Http\Controllers\Home\ProfileController::class, 'addAddress'])->name('home.profile.addresses.add');
        Route::put('/addresses/{id}', [\Jiny\Auth\App\Http\Controllers\Home\ProfileController::class, 'updateAddress'])->name('home.profile.addresses.update');
        Route::delete('/addresses/{id}', [\Jiny\Auth\App\Http\Controllers\Home\ProfileController::class, 'deleteAddress'])->name('home.profile.addresses.delete');
        Route::get('/security', [\Jiny\Auth\App\Http\Controllers\Home\ProfileController::class, 'security'])->name('home.profile.security');
        Route::post('/security/2fa', [\Jiny\Auth\App\Http\Controllers\Home\ProfileController::class, 'enable2FA'])->name('home.profile.security.2fa');
        Route::get('/social', [\Jiny\Auth\App\Http\Controllers\Home\ProfileController::class, 'socialAccounts'])->name('home.profile.social');
        Route::delete('/social/{provider}', [\Jiny\Auth\App\Http\Controllers\Home\ProfileController::class, 'disconnectSocial'])->name('home.profile.social.disconnect');
    });
    
    // 계정 설정
    Route::get('/settings', [HomeController::class, 'settings'])->name('home.settings');
    Route::put('/settings', [HomeController::class, 'updateSettings'])->name('home.settings.update');
    
    // 비밀번호 변경
    Route::get('/account/password', [PasswordController::class, 'showChangeForm'])->name('home.account.password');
    Route::post('/account/password', [PasswordController::class, 'update'])->name('home.account.password.update');
    Route::get('/account/password/force-change', [PasswordController::class, 'forceChangeForm'])->name('home.account.password.force');
    Route::post('/account/password/force-change', [PasswordController::class, 'forceChange'])->name('home.account.password.force.update');
    
    // 계정 삭제
    Route::get('/account/delete', [HomeController::class, 'deleteForm'])->name('home.account.delete');
    Route::delete('/account', [HomeController::class, 'deleteAccount'])->name('home.account.destroy');
    
    // 세션 관리
    Route::get('/account/sessions', [\Jiny\Auth\App\Http\Controllers\Home\SessionController::class, 'index'])->name('home.account.sessions');
    Route::get('/account/sessions/{id}/details', [\Jiny\Auth\App\Http\Controllers\Home\SessionController::class, 'details'])->name('home.account.sessions.details');
    Route::post('/account/sessions/{id}/terminate', [\Jiny\Auth\App\Http\Controllers\Home\SessionController::class, 'terminate'])->name('home.account.sessions.terminate');
    Route::post('/account/sessions/terminate-all', [\Jiny\Auth\App\Http\Controllers\Home\SessionController::class, 'terminateAll'])->name('home.account.sessions.terminate-all');
    
    // JWT 토큰 관리
    Route::get('/account/tokens', [\Jiny\Auth\App\Http\Controllers\Home\TokenController::class, 'index'])->name('home.account.tokens');
    Route::get('/account/tokens/active', [\Jiny\Auth\App\Http\Controllers\Home\TokenController::class, 'active'])->name('home.account.tokens.active');
    Route::delete('/account/tokens/{id}', [\Jiny\Auth\App\Http\Controllers\Home\TokenController::class, 'destroy'])->name('home.account.tokens.destroy');
    Route::post('/account/tokens/revoke-all', [\Jiny\Auth\App\Http\Controllers\Home\TokenController::class, 'revokeAll'])->name('home.account.tokens.revoke-all');
    Route::get('/account/tokens/history', [\Jiny\Auth\App\Http\Controllers\Home\TokenController::class, 'history'])->name('home.account.tokens.history');
    
    // 휴면계정 관리
    Route::get('/account/dormant', [\Jiny\Auth\App\Http\Controllers\Auth\DormantController::class, 'status'])->name('home.dormant.status');
    Route::post('/account/dormant/extend', [\Jiny\Auth\App\Http\Controllers\Auth\DormantController::class, 'extend'])->name('home.dormant.extend');
    
    // 소셜 계정 관리
    Route::prefix('account/social')->group(function () {
        Route::get('/', [\Jiny\Auth\App\Http\Controllers\Home\SocialAccountController::class, 'index'])->name('home.account.social');
        Route::post('/{provider}/connect', [\Jiny\Auth\App\Http\Controllers\Home\SocialAccountController::class, 'connect'])->name('home.account.social.connect');
        Route::delete('/{provider}/disconnect', [\Jiny\Auth\App\Http\Controllers\Home\SocialAccountController::class, 'disconnect'])->name('home.account.social.disconnect');
    });
    
    // eMoney 관리
    Route::prefix('emoney')->group(function () {
        Route::get('/', [\Jiny\Auth\App\Http\Controllers\User\UserEmoneyController::class, 'index'])->name('home.emoney');
        Route::get('/deposit', [\Jiny\Auth\App\Http\Controllers\User\UserEmoneyController::class, 'depositForm'])->name('home.emoney.deposit');
        Route::post('/deposit', [\Jiny\Auth\App\Http\Controllers\User\UserEmoneyController::class, 'deposit'])->name('home.emoney.deposit.post');
        Route::get('/withdraw', [\Jiny\Auth\App\Http\Controllers\User\UserEmoneyController::class, 'withdrawForm'])->name('home.emoney.withdraw');
        Route::post('/withdraw', [\Jiny\Auth\App\Http\Controllers\User\UserEmoneyController::class, 'withdraw'])->name('home.emoney.withdraw.post');
        Route::get('/bank', [\Jiny\Auth\App\Http\Controllers\User\UserEmoneyController::class, 'bankAccounts'])->name('home.emoney.bank');
        Route::post('/bank', [\Jiny\Auth\App\Http\Controllers\User\UserEmoneyController::class, 'addBankAccount'])->name('home.emoney.bank.add');
        Route::put('/bank/{id}', [\Jiny\Auth\App\Http\Controllers\User\UserEmoneyController::class, 'updateBankAccount'])->name('home.emoney.bank.update');
        Route::delete('/bank/{id}', [\Jiny\Auth\App\Http\Controllers\User\UserEmoneyController::class, 'deleteBankAccount'])->name('home.emoney.bank.delete');
    });
});

// 인증이 필요한 라우트 (기존 호환성 유지)
Route::middleware(['auth'])->group(function () {
    // 대시보드
    Route::get('/dashboard', function() {
        return redirect()->route('home');
    })->name('dashboard');
    
    // 2단계 인증 설정 (나중에 구현)
    // Route::get('/two-factor', 'AuthTwoFactorSettings@index')->name('two-factor.index');
    // Route::post('/two-factor/enable', 'AuthTwoFactorSettings@enable')->name('two-factor.enable');
    // Route::post('/two-factor/disable', 'AuthTwoFactorSettings@disable')->name('two-factor.disable');
    // Route::post('/two-factor/verify', 'AuthTwoFactorSettings@verify')->name('two-factor.verify');
    // Route::get('/two-factor/recovery-codes', 'AuthTwoFactorSettings@recoveryCodes')->name('two-factor.recovery-codes');
    // Route::post('/two-factor/recovery-codes', 'AuthTwoFactorSettings@regenerateRecoveryCodes')->name('two-factor.recovery-codes.regenerate');
    
    // 세션 관리 (나중에 구현)
    // Route::get('/sessions', 'AuthSessions@index')->name('sessions.index');
    // Route::delete('/sessions/{id}', 'AuthSessions@destroy')->name('sessions.destroy');
    // Route::post('/sessions/logout-other', 'AuthSessions@logoutOther')->name('sessions.logout-other');
});

/**
 * JWT 기반 인증 라우트
 * /signin, /signup, /signout
 */

// JWT 로그인 (signin)
Route::get('/signin', [AuthJwtSigninController::class, 'index'])->name('jwt.signin');
Route::post('/signin', [AuthJwtSigninController::class, 'signin'])->name('jwt.signin.post');
Route::get('/signin/refresh', [AuthJwtSigninController::class, 'refresh'])->name('jwt.signin.refresh');

// JWT 회원가입 (signup)
Route::get('/signup', [AuthJwtSignupController::class, 'index'])->name('jwt.signup');
Route::post('/signup', [AuthJwtSignupController::class, 'signup'])->name('jwt.signup.post');

// JWT 로그아웃 (signout)
Route::get('/signout', [AuthJwtSignoutController::class, 'index'])->name('jwt.signout')->middleware('auth:api');
Route::post('/signout', [AuthJwtSignoutController::class, 'signout'])->name('jwt.signout.post')->middleware('auth:api');
Route::post('/signout/all', [AuthJwtSignoutController::class, 'signoutAll'])->name('jwt.signout.all')->middleware('auth:api');

/**
 * 소셜 로그인 라우트
 */
Route::middleware(['web'])->prefix('login')->group(function () {
    // 소셜 로그인 (각 공급자별)
    Route::get('/google', [\Jiny\Auth\App\Http\Controllers\Social\OAuthController::class, 'redirect'])->defaults('provider', 'google')->name('login.google');
    Route::get('/google/callback', [\Jiny\Auth\App\Http\Controllers\Social\OAuthController::class, 'callback'])->defaults('provider', 'google')->name('login.google.callback');
    
    Route::get('/facebook', [\Jiny\Auth\App\Http\Controllers\Social\OAuthController::class, 'redirect'])->defaults('provider', 'facebook')->name('login.facebook');
    Route::get('/facebook/callback', [\Jiny\Auth\App\Http\Controllers\Social\OAuthController::class, 'callback'])->defaults('provider', 'facebook')->name('login.facebook.callback');
    
    Route::get('/github', [\Jiny\Auth\App\Http\Controllers\Social\OAuthController::class, 'redirect'])->defaults('provider', 'github')->name('login.github');
    Route::get('/github/callback', [\Jiny\Auth\App\Http\Controllers\Social\OAuthController::class, 'callback'])->defaults('provider', 'github')->name('login.github.callback');
    
    Route::get('/naver', [\Jiny\Auth\App\Http\Controllers\Social\OAuthController::class, 'redirect'])->defaults('provider', 'naver')->name('login.naver');
    Route::get('/naver/callback', [\Jiny\Auth\App\Http\Controllers\Social\OAuthController::class, 'callback'])->defaults('provider', 'naver')->name('login.naver.callback');
    
    Route::get('/kakao', [\Jiny\Auth\App\Http\Controllers\Social\OAuthController::class, 'redirect'])->defaults('provider', 'kakao')->name('login.kakao');
    Route::get('/kakao/callback', [\Jiny\Auth\App\Http\Controllers\Social\OAuthController::class, 'callback'])->defaults('provider', 'kakao')->name('login.kakao.callback');
});

// 프로필 관리 라우트는 /home/profile 로 이동됨 (위 참조)

/**
 * E-Money 관리 라우트
 */
Route::middleware(['auth'])->prefix('emoney')->group(function () {
    // 잔액 조회
    Route::get('/', [\Jiny\Auth\App\Http\Controllers\Emoney\Home\SiteUserEmoney::class, 'index'])->name('emoney.index');
    
    // 입금
    Route::get('/deposit', [\Jiny\Auth\App\Http\Controllers\Emoney\Home\SiteUserEmoneyDeposit::class, 'index'])->name('emoney.deposit');
    Route::post('/deposit', [\Jiny\Auth\App\Http\Controllers\Emoney\Home\SiteUserEmoneyDeposit::class, 'store'])->name('emoney.deposit.store');
    
    // 출금
    Route::get('/withdraw', [\Jiny\Auth\App\Http\Controllers\Emoney\Home\SiteUserEmoneyWithdraw::class, 'index'])->name('emoney.withdraw');
    Route::post('/withdraw', [\Jiny\Auth\App\Http\Controllers\Emoney\Home\SiteUserEmoneyWithdraw::class, 'store'])->name('emoney.withdraw.store');
    
    // 은행 계좌 관리
    Route::get('/bank', [\Jiny\Auth\App\Http\Controllers\Emoney\Home\SiteUserEmoneyBank::class, 'index'])->name('emoney.bank');
    Route::post('/bank', [\Jiny\Auth\App\Http\Controllers\Emoney\Home\SiteUserEmoneyBank::class, 'store'])->name('emoney.bank.store');
    Route::put('/bank/{id}', [\Jiny\Auth\App\Http\Controllers\Emoney\Home\SiteUserEmoneyBank::class, 'update'])->name('emoney.bank.update');
    Route::delete('/bank/{id}', [\Jiny\Auth\App\Http\Controllers\Emoney\Home\SiteUserEmoneyBank::class, 'destroy'])->name('emoney.bank.delete');
});

/**
 * 사용자 홈 메뉴
 */
Route::middleware(['auth'])->prefix('home')->group(function () {
    // 메시지 관리
    Route::prefix('message')->group(function () {
        Route::get('/', [\Jiny\Auth\App\Http\Controllers\Home\MessageController::class, 'index'])->name('home.message');
        Route::get('/compose', [\Jiny\Auth\App\Http\Controllers\Home\MessageController::class, 'compose'])->name('home.message.compose');
        Route::post('/', [\Jiny\Auth\App\Http\Controllers\Home\MessageController::class, 'send'])->name('home.message.send');
        Route::get('/blocked/users', [\Jiny\Auth\App\Http\Controllers\Home\MessageController::class, 'blockedUsers'])->name('home.message.blocked');
        Route::get('/settings/notifications', [\Jiny\Auth\App\Http\Controllers\Home\MessageController::class, 'settings'])->name('home.message.settings');
        Route::post('/settings/notifications', [\Jiny\Auth\App\Http\Controllers\Home\MessageController::class, 'updateSettings'])->name('home.message.settings.update');
        Route::get('/{id}', [\Jiny\Auth\App\Http\Controllers\Home\MessageController::class, 'show'])->name('home.message.show');
        Route::post('/{id}/read', [\Jiny\Auth\App\Http\Controllers\Home\MessageController::class, 'markAsRead'])->name('home.message.read');
        Route::post('/{id}/star', [\Jiny\Auth\App\Http\Controllers\Home\MessageController::class, 'toggleStar'])->name('home.message.star');
        Route::post('/{id}/archive', [\Jiny\Auth\App\Http\Controllers\Home\MessageController::class, 'archive'])->name('home.message.archive');
        Route::delete('/{id}', [\Jiny\Auth\App\Http\Controllers\Home\MessageController::class, 'destroy'])->name('home.message.destroy');
        Route::post('/block', [\Jiny\Auth\App\Http\Controllers\Home\MessageController::class, 'blockUser'])->name('home.message.block');
        Route::delete('/block/{userId}', [\Jiny\Auth\App\Http\Controllers\Home\MessageController::class, 'unblockUser'])->name('home.message.unblock');
    });
});

/**
 * 사용자 메시지 및 리뷰 라우트 (레거시)
 */
Route::middleware(['auth'])->prefix('messages')->group(function () {
    // 메시지
    Route::get('/', [\Jiny\Auth\App\Http\Livewire\Users\HomeUserMessage::class, 'render'])->name('messages.index');
    Route::post('/', [\Jiny\Auth\App\Http\Livewire\Users\HomeUserMessage::class, 'send'])->name('messages.send');
    
    // 리뷰
    Route::get('/reviews', [\Jiny\Auth\App\Http\Livewire\Users\HomeUserReviews::class, 'render'])->name('reviews.index');
    Route::post('/reviews', [\Jiny\Auth\App\Http\Livewire\Users\HomeUserReviews::class, 'store'])->name('reviews.store');
});