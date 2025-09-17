<?php

use Illuminate\Support\Facades\Route;

/**
 * 관리자 인증 관련 라우트
 * 
 * @jiny/admin 패키지가 설치되어 있을 때만 활성화됩니다.
 */

// 관리자 라우트는 @jiny/admin이 설치된 후 활성화됩니다.
if (class_exists('Jiny\Admin\JinyAdminServiceProvider')) {
    Route::middleware(['web', 'auth', 'admin'])->prefix('admin')->group(function () {
        
        // 약관 관리
        Route::prefix('terms')->group(function () {
            Route::get('/', [\Jiny\Auth\App\Http\Controllers\Admin\Terms\AdminAuthTermsController::class, 'index'])->name('admin.terms.index');
            Route::get('/create', [\Jiny\Auth\App\Http\Controllers\Admin\Terms\AdminAuthTermsController::class, 'create'])->name('admin.terms.create');
            Route::post('/', [\Jiny\Auth\App\Http\Controllers\Admin\Terms\AdminAuthTermsController::class, 'store'])->name('admin.terms.store');
            Route::get('/{id}/edit', [\Jiny\Auth\App\Http\Controllers\Admin\Terms\AdminAuthTermsController::class, 'edit'])->name('admin.terms.edit');
            Route::put('/{id}', [\Jiny\Auth\App\Http\Controllers\Admin\Terms\AdminAuthTermsController::class, 'update'])->name('admin.terms.update');
            Route::delete('/{id}', [\Jiny\Auth\App\Http\Controllers\Admin\Terms\AdminAuthTermsController::class, 'destroy'])->name('admin.terms.destroy');
            
            // 약관 동의 로그
            Route::get('/logs', [\Jiny\Auth\App\Http\Controllers\Admin\Terms\AdminAuthTermsLogsController::class, 'index'])->name('admin.terms.logs');
        });
        
        // 비밀번호 오류 관리
        Route::prefix('password-errors')->group(function () {
            Route::get('/', [\Jiny\Auth\App\Http\Controllers\Admin\AdminPasswordErrorController::class, 'index'])->name('admin.password-errors.index');
            Route::get('/locked-accounts', [\Jiny\Auth\App\Http\Controllers\Admin\AdminPasswordErrorController::class, 'lockedAccounts'])->name('admin.password-errors.locked');
            Route::post('/unlock/{userId}', [\Jiny\Auth\App\Http\Controllers\Admin\AdminPasswordErrorController::class, 'unlock'])->name('admin.password-errors.unlock');
            Route::get('/statistics', [\Jiny\Auth\App\Http\Controllers\Admin\AdminPasswordErrorController::class, 'statistics'])->name('admin.password-errors.statistics');
        });
        
        // 회원가입 승인 관리
        Route::prefix('approval')->group(function () {
            Route::get('/', [\Jiny\Auth\App\Http\Controllers\Admin\AdminApprovalController::class, 'index'])->name('admin.auth.approval');
            Route::get('/{id}', [\Jiny\Auth\App\Http\Controllers\Admin\AdminApprovalController::class, 'show'])->name('admin.auth.approval.show');
            Route::post('/{id}/approve', [\Jiny\Auth\App\Http\Controllers\Admin\AdminApprovalController::class, 'approve'])->name('admin.auth.approval.approve');
            Route::post('/{id}/reject', [\Jiny\Auth\App\Http\Controllers\Admin\AdminApprovalController::class, 'reject'])->name('admin.auth.approval.reject');
            Route::post('/bulk-approve', [\Jiny\Auth\App\Http\Controllers\Admin\AdminApprovalController::class, 'bulkApprove'])->name('admin.auth.approval.bulk-approve');
            Route::post('/bulk-reject', [\Jiny\Auth\App\Http\Controllers\Admin\AdminApprovalController::class, 'bulkReject'])->name('admin.auth.approval.bulk-reject');
        });
        
        // 샤딩 관리 (고급 기능)
        Route::prefix('sharding')->group(function () {
            Route::get('/', [\Jiny\Auth\App\Http\Controllers\Admin\Sharding\AdminShardingController::class, 'index'])->name('admin.sharding.index');
            Route::get('/dashboard', [\Jiny\Auth\App\Http\Controllers\Admin\Sharding\AdminShardingController::class, 'dashboard'])->name('admin.sharding.dashboard');
            Route::post('/create-shard', [\Jiny\Auth\App\Http\Controllers\Admin\Sharding\AdminShardingController::class, 'createShard'])->name('admin.sharding.create');
        });
        
        // 2FA 관리
        Route::prefix('auth/2fa')->group(function () {
            Route::get('/settings', [\Jiny\Auth\App\Http\Controllers\Admin\Admin2FAController::class, 'settings'])->name('admin.2fa.settings');
            Route::post('/settings', [\Jiny\Auth\App\Http\Controllers\Admin\Admin2FAController::class, 'updateSettings'])->name('admin.2fa.settings.update');
            Route::get('/users', [\Jiny\Auth\App\Http\Controllers\Admin\Admin2FAController::class, 'users'])->name('admin.2fa.users');
            Route::post('/users/{id}/disable', [\Jiny\Auth\App\Http\Controllers\Admin\Admin2FAController::class, 'disableUser'])->name('admin.2fa.users.disable');
            Route::post('/users/{id}/force-enable', [\Jiny\Auth\App\Http\Controllers\Admin\Admin2FAController::class, 'forceEnableUser'])->name('admin.2fa.users.force-enable');
            Route::post('/users/{id}/toggle', [\Jiny\Auth\App\Http\Controllers\Admin\Admin2FAController::class, 'toggleUser'])->name('admin.2fa.users.toggle');
            Route::get('/users/{id}/details', [\Jiny\Auth\App\Http\Controllers\Admin\Admin2FAController::class, 'userDetails'])->name('admin.2fa.users.details');
            Route::get('/statistics', [\Jiny\Auth\App\Http\Controllers\Admin\Admin2FAController::class, 'statistics'])->name('admin.2fa.statistics');
            Route::post('/request-all', [\Jiny\Auth\App\Http\Controllers\Admin\Admin2FAController::class, 'requestAll'])->name('admin.2fa.request-all');
        });
        
        // 세션 관리
        Route::prefix('auth/sessions')->group(function () {
            Route::get('/', [\Jiny\Auth\App\Http\Controllers\Admin\AdminSessionController::class, 'index'])->name('admin.auth.sessions');
            Route::get('/{id}/details', [\Jiny\Auth\App\Http\Controllers\Admin\AdminSessionController::class, 'details'])->name('admin.auth.sessions.details');
            Route::post('/{id}/terminate', [\Jiny\Auth\App\Http\Controllers\Admin\AdminSessionController::class, 'terminate'])->name('admin.auth.sessions.terminate');
            Route::post('/bulk-terminate', [\Jiny\Auth\App\Http\Controllers\Admin\AdminSessionController::class, 'bulkTerminate'])->name('admin.auth.sessions.bulk-terminate');
            Route::get('/statistics', [\Jiny\Auth\App\Http\Controllers\Admin\AdminSessionController::class, 'statistics'])->name('admin.auth.sessions.statistics');
        });
        
        // 블랙리스트 관리
        Route::prefix('auth/blacklist')->group(function () {
            Route::get('/', [\Jiny\Auth\App\Http\Controllers\Admin\AdminBlacklistController::class, 'index'])->name('admin.auth.blacklist');
            Route::get('/email', [\Jiny\Auth\App\Http\Controllers\Admin\AdminBlacklistController::class, 'emailList'])->name('admin.auth.blacklist.email');
            Route::get('/ip', [\Jiny\Auth\App\Http\Controllers\Admin\AdminBlacklistController::class, 'ipList'])->name('admin.auth.blacklist.ip');
            Route::post('/email', [\Jiny\Auth\App\Http\Controllers\Admin\AdminBlacklistController::class, 'addEmail'])->name('admin.auth.blacklist.email.add');
            Route::post('/ip', [\Jiny\Auth\App\Http\Controllers\Admin\AdminBlacklistController::class, 'addIp'])->name('admin.auth.blacklist.ip.add');
            Route::put('/{id}', [\Jiny\Auth\App\Http\Controllers\Admin\AdminBlacklistController::class, 'update'])->name('admin.auth.blacklist.update');
            Route::delete('/{id}', [\Jiny\Auth\App\Http\Controllers\Admin\AdminBlacklistController::class, 'destroy'])->name('admin.auth.blacklist.destroy');
            Route::post('/bulk-add', [\Jiny\Auth\App\Http\Controllers\Admin\AdminBlacklistController::class, 'bulkAdd'])->name('admin.auth.blacklist.bulk-add');
            Route::post('/bulk-remove', [\Jiny\Auth\App\Http\Controllers\Admin\AdminBlacklistController::class, 'bulkRemove'])->name('admin.auth.blacklist.bulk-remove');
            Route::get('/whitelist', [\Jiny\Auth\App\Http\Controllers\Admin\AdminBlacklistController::class, 'whitelist'])->name('admin.auth.blacklist.whitelist');
            Route::post('/whitelist', [\Jiny\Auth\App\Http\Controllers\Admin\AdminBlacklistController::class, 'addWhitelist'])->name('admin.auth.blacklist.whitelist.add');
        });
        
        // JWT 토큰 관리
        Route::prefix('auth/jwt')->group(function () {
            Route::get('/tokens', [\Jiny\Auth\App\Http\Controllers\Admin\AdminJWTController::class, 'index'])->name('admin.auth.jwt.tokens');
            Route::get('/tokens/active', [\Jiny\Auth\App\Http\Controllers\Admin\AdminJWTController::class, 'active'])->name('admin.auth.jwt.tokens.active');
            Route::get('/tokens/expired', [\Jiny\Auth\App\Http\Controllers\Admin\AdminJWTController::class, 'expired'])->name('admin.auth.jwt.tokens.expired');
            Route::get('/tokens/{id}', [\Jiny\Auth\App\Http\Controllers\Admin\AdminJWTController::class, 'show'])->name('admin.auth.jwt.tokens.show');
            Route::delete('/tokens/{id}', [\Jiny\Auth\App\Http\Controllers\Admin\AdminJWTController::class, 'destroy'])->name('admin.auth.jwt.tokens.destroy');
            Route::post('/tokens/revoke-all', [\Jiny\Auth\App\Http\Controllers\Admin\AdminJWTController::class, 'revokeAll'])->name('admin.auth.jwt.tokens.revoke-all');
            Route::post('/tokens/revoke-user/{id}', [\Jiny\Auth\App\Http\Controllers\Admin\AdminJWTController::class, 'revokeUser'])->name('admin.auth.jwt.tokens.revoke-user');
            Route::get('/settings', [\Jiny\Auth\App\Http\Controllers\Admin\AdminJWTController::class, 'settings'])->name('admin.auth.jwt.settings');
            Route::post('/settings', [\Jiny\Auth\App\Http\Controllers\Admin\AdminJWTController::class, 'updateSettings'])->name('admin.auth.jwt.settings.update');
            Route::get('/statistics', [\Jiny\Auth\App\Http\Controllers\Admin\AdminJWTController::class, 'statistics'])->name('admin.auth.jwt.statistics');
        });
        
        // 휴면계정 관리
        Route::prefix('auth/users/dormant')->group(function () {
            Route::get('/', [\Jiny\Auth\App\Http\Controllers\Admin\AdminDormantController::class, 'index'])->name('admin.auth.users.dormant');
            Route::get('/statistics', [\Jiny\Auth\App\Http\Controllers\Admin\AdminDormantController::class, 'statistics'])->name('admin.auth.users.dormant.statistics');
            Route::post('/{id}/activate', [\Jiny\Auth\App\Http\Controllers\Admin\AdminDormantController::class, 'activate'])->name('admin.auth.users.dormant.activate');
            Route::post('/{id}/delete', [\Jiny\Auth\App\Http\Controllers\Admin\AdminDormantController::class, 'delete'])->name('admin.auth.users.dormant.delete');
            Route::post('/bulk-activate', [\Jiny\Auth\App\Http\Controllers\Admin\AdminDormantController::class, 'bulkActivate'])->name('admin.auth.users.dormant.bulk-activate');
            Route::post('/bulk-delete', [\Jiny\Auth\App\Http\Controllers\Admin\AdminDormantController::class, 'bulkDelete'])->name('admin.auth.users.dormant.bulk-delete');
            Route::get('/settings', [\Jiny\Auth\App\Http\Controllers\Admin\AdminDormantController::class, 'settings'])->name('admin.auth.users.dormant.settings');
            Route::post('/settings', [\Jiny\Auth\App\Http\Controllers\Admin\AdminDormantController::class, 'updateSettings'])->name('admin.auth.users.dormant.settings.update');
        });
        
        // 사용자 프로필 관리
        Route::prefix('auth/users/{id}/profile')->group(function () {
            Route::get('/', [\Jiny\Auth\App\Http\Controllers\Admin\AdminProfileController::class, 'show'])->name('admin.auth.users.profile');
            Route::put('/', [\Jiny\Auth\App\Http\Controllers\Admin\AdminProfileController::class, 'update'])->name('admin.auth.users.profile.update');
            Route::post('/avatar', [\Jiny\Auth\App\Http\Controllers\Admin\AdminProfileController::class, 'uploadAvatar'])->name('admin.auth.users.avatar.upload');
            Route::delete('/avatar', [\Jiny\Auth\App\Http\Controllers\Admin\AdminProfileController::class, 'deleteAvatar'])->name('admin.auth.users.avatar.delete');
            Route::get('/history', [\Jiny\Auth\App\Http\Controllers\Admin\AdminProfileController::class, 'history'])->name('admin.auth.users.profile.history');
        });
        
        // 사용자 추가정보 관리
        Route::prefix('auth/users/{id}/additional')->group(function () {
            Route::get('/', [\Jiny\Auth\App\Http\Controllers\Admin\AdminProfileController::class, 'additional'])->name('admin.auth.users.additional');
            Route::put('/', [\Jiny\Auth\App\Http\Controllers\Admin\AdminProfileController::class, 'updateAdditional'])->name('admin.auth.users.additional.update');
        });
        
        // 소셜 로그인 관리
        Route::prefix('auth/social')->group(function () {
            Route::get('/', [\Jiny\Auth\App\Http\Controllers\Admin\AdminSocialController::class, 'index'])->name('admin.auth.social');
            Route::get('/statistics', [\Jiny\Auth\App\Http\Controllers\Admin\AdminSocialController::class, 'statistics'])->name('admin.auth.social.statistics');
        });
        
        // OAuth 공급자 관리
        Route::prefix('auth/oauth')->group(function () {
            Route::get('/', [\Jiny\Auth\App\Http\Controllers\Admin\AdminSocialController::class, 'oauth'])->name('admin.auth.oauth');
            Route::put('/{id}', [\Jiny\Auth\App\Http\Controllers\Admin\AdminSocialController::class, 'updateProvider'])->name('admin.auth.oauth.update');
            Route::get('/users/{provider}', [\Jiny\Auth\App\Http\Controllers\Admin\AdminSocialController::class, 'users'])->name('admin.auth.oauth.users');
        });
        
        // 소셜 계정 관리
        Route::prefix('auth/social/accounts')->group(function () {
            Route::get('/{id}', [\Jiny\Auth\App\Http\Controllers\Admin\AdminSocialController::class, 'accountDetails'])->name('admin.auth.social.accounts.details');
            Route::delete('/{id}', [\Jiny\Auth\App\Http\Controllers\Admin\AdminSocialController::class, 'disconnectAccount'])->name('admin.auth.social.accounts.disconnect');
        });
        
        // 메시지 관리
        Route::prefix('auth/message')->group(function () {
            Route::get('/', [\Jiny\Auth\App\Http\Controllers\Admin\AdminMessageController::class, 'index'])->name('admin.auth.message');
            Route::get('/compose', [\Jiny\Auth\App\Http\Controllers\Admin\AdminMessageController::class, 'compose'])->name('admin.auth.message.compose');
            Route::post('/', [\Jiny\Auth\App\Http\Controllers\Admin\AdminMessageController::class, 'send'])->name('admin.auth.message.send');
            Route::get('/sse', [\Jiny\Auth\App\Http\Controllers\Admin\AdminMessageController::class, 'sseTest'])->name('admin.auth.message.sse');
            Route::get('/sse/stream', [\Jiny\Auth\App\Http\Controllers\Admin\AdminMessageController::class, 'sseStream'])->name('admin.auth.message.sse.stream');
            Route::get('/statistics', [\Jiny\Auth\App\Http\Controllers\Admin\AdminMessageController::class, 'statistics'])->name('admin.auth.message.statistics');
            Route::get('/blocked', [\Jiny\Auth\App\Http\Controllers\Admin\AdminMessageController::class, 'blockedUsers'])->name('admin.auth.message.blocked');
            Route::delete('/blocked/{id}', [\Jiny\Auth\App\Http\Controllers\Admin\AdminMessageController::class, 'unblock'])->name('admin.auth.message.unblock');
            Route::get('/templates', [\Jiny\Auth\App\Http\Controllers\Admin\AdminMessageController::class, 'templates'])->name('admin.auth.message.templates');
            Route::get('/templates/create', [\Jiny\Auth\App\Http\Controllers\Admin\AdminMessageController::class, 'createTemplate'])->name('admin.auth.message.templates.create');
            Route::post('/templates', [\Jiny\Auth\App\Http\Controllers\Admin\AdminMessageController::class, 'storeTemplate'])->name('admin.auth.message.templates.store');
            Route::get('/templates/{id}/edit', [\Jiny\Auth\App\Http\Controllers\Admin\AdminMessageController::class, 'editTemplate'])->name('admin.auth.message.templates.edit');
            Route::put('/templates/{id}', [\Jiny\Auth\App\Http\Controllers\Admin\AdminMessageController::class, 'updateTemplate'])->name('admin.auth.message.templates.update');
            Route::delete('/templates/{id}', [\Jiny\Auth\App\Http\Controllers\Admin\AdminMessageController::class, 'deleteTemplate'])->name('admin.auth.message.templates.delete');
            Route::get('/{id}', [\Jiny\Auth\App\Http\Controllers\Admin\AdminMessageController::class, 'show'])->name('admin.auth.message.show');
        });
        
        // 언어 관리
        Route::prefix('auth/languages')->group(function () {
            Route::get('/', [\Jiny\Auth\App\Http\Controllers\Admin\AdminLanguageController::class, 'index'])->name('admin.auth.languages');
            Route::get('/create', [\Jiny\Auth\App\Http\Controllers\Admin\AdminLanguageController::class, 'create'])->name('admin.auth.languages.create');
            Route::post('/', [\Jiny\Auth\App\Http\Controllers\Admin\AdminLanguageController::class, 'store'])->name('admin.auth.languages.store');
            Route::get('/{id}/edit', [\Jiny\Auth\App\Http\Controllers\Admin\AdminLanguageController::class, 'edit'])->name('admin.auth.languages.edit');
            Route::put('/{id}', [\Jiny\Auth\App\Http\Controllers\Admin\AdminLanguageController::class, 'update'])->name('admin.auth.languages.update');
            Route::delete('/{id}', [\Jiny\Auth\App\Http\Controllers\Admin\AdminLanguageController::class, 'destroy'])->name('admin.auth.languages.destroy');
            Route::post('/reorder', [\Jiny\Auth\App\Http\Controllers\Admin\AdminLanguageController::class, 'reorder'])->name('admin.auth.languages.reorder');
            Route::get('/{id}/users', [\Jiny\Auth\App\Http\Controllers\Admin\AdminLanguageController::class, 'users'])->name('admin.auth.languages.users');
        });
        
        // 국가 관리
        Route::prefix('auth/countries')->group(function () {
            Route::get('/', [\Jiny\Auth\App\Http\Controllers\Admin\AdminCountryController::class, 'index'])->name('admin.auth.countries');
            Route::get('/create', [\Jiny\Auth\App\Http\Controllers\Admin\AdminCountryController::class, 'create'])->name('admin.auth.countries.create');
            Route::post('/', [\Jiny\Auth\App\Http\Controllers\Admin\AdminCountryController::class, 'store'])->name('admin.auth.countries.store');
            Route::get('/{id}/edit', [\Jiny\Auth\App\Http\Controllers\Admin\AdminCountryController::class, 'edit'])->name('admin.auth.countries.edit');
            Route::put('/{id}', [\Jiny\Auth\App\Http\Controllers\Admin\AdminCountryController::class, 'update'])->name('admin.auth.countries.update');
            Route::delete('/{id}', [\Jiny\Auth\App\Http\Controllers\Admin\AdminCountryController::class, 'destroy'])->name('admin.auth.countries.destroy');
            Route::get('/statistics', [\Jiny\Auth\App\Http\Controllers\Admin\AdminCountryController::class, 'statistics'])->name('admin.auth.countries.statistics');
            Route::post('/import', [\Jiny\Auth\App\Http\Controllers\Admin\AdminCountryController::class, 'import'])->name('admin.auth.countries.import');
        });
        
        // 이메일 템플릿 관리
        Route::prefix('auth/emails/templates')->group(function () {
            Route::get('/', [\Jiny\Auth\App\Http\Controllers\Admin\AdminEmailTemplateController::class, 'index'])->name('admin.auth.emails.templates');
            Route::get('/create', [\Jiny\Auth\App\Http\Controllers\Admin\AdminEmailTemplateController::class, 'create'])->name('admin.auth.emails.templates.create');
            Route::post('/', [\Jiny\Auth\App\Http\Controllers\Admin\AdminEmailTemplateController::class, 'store'])->name('admin.auth.emails.templates.store');
            Route::get('/{id}/edit', [\Jiny\Auth\App\Http\Controllers\Admin\AdminEmailTemplateController::class, 'edit'])->name('admin.auth.emails.templates.edit');
            Route::put('/{id}', [\Jiny\Auth\App\Http\Controllers\Admin\AdminEmailTemplateController::class, 'update'])->name('admin.auth.emails.templates.update');
            Route::delete('/{id}', [\Jiny\Auth\App\Http\Controllers\Admin\AdminEmailTemplateController::class, 'destroy'])->name('admin.auth.emails.templates.destroy');
            Route::get('/{id}/preview', [\Jiny\Auth\App\Http\Controllers\Admin\AdminEmailTemplateController::class, 'preview'])->name('admin.auth.emails.templates.preview');
            Route::post('/{id}/duplicate', [\Jiny\Auth\App\Http\Controllers\Admin\AdminEmailTemplateController::class, 'duplicate'])->name('admin.auth.emails.templates.duplicate');
        });
        
        // 이메일 발송 관리
        Route::prefix('auth/emails')->group(function () {
            Route::get('/send', [\Jiny\Auth\App\Http\Controllers\Admin\AdminEmailSendController::class, 'create'])->name('admin.auth.emails.send');
            Route::post('/send', [\Jiny\Auth\App\Http\Controllers\Admin\AdminEmailSendController::class, 'send'])->name('admin.auth.emails.send.post');
            Route::get('/logs', [\Jiny\Auth\App\Http\Controllers\Admin\AdminEmailSendController::class, 'logs'])->name('admin.auth.emails.logs');
            Route::get('/logs/{id}', [\Jiny\Auth\App\Http\Controllers\Admin\AdminEmailSendController::class, 'show'])->name('admin.auth.emails.logs.show');
            Route::post('/logs/{id}/resend', [\Jiny\Auth\App\Http\Controllers\Admin\AdminEmailSendController::class, 'resend'])->name('admin.auth.emails.logs.resend');
            Route::get('/bulk', [\Jiny\Auth\App\Http\Controllers\Admin\AdminEmailSendController::class, 'bulkList'])->name('admin.auth.emails.bulk');
        });
        
        // SMS 관리
        Route::prefix('auth/sms')->group(function () {
            Route::get('/send', [\Jiny\Auth\App\Http\Controllers\Admin\AdminSmsController::class, 'create'])->name('admin.auth.sms.send');
            Route::post('/send', [\Jiny\Auth\App\Http\Controllers\Admin\AdminSmsController::class, 'send'])->name('admin.auth.sms.send.post');
            Route::get('/logs', [\Jiny\Auth\App\Http\Controllers\Admin\AdminSmsController::class, 'logs'])->name('admin.auth.sms.logs');
            Route::get('/templates', [\Jiny\Auth\App\Http\Controllers\Admin\AdminSmsController::class, 'templates'])->name('admin.auth.sms.templates');
            Route::get('/templates/create', [\Jiny\Auth\App\Http\Controllers\Admin\AdminSmsController::class, 'createTemplate'])->name('admin.auth.sms.templates.create');
            Route::post('/templates', [\Jiny\Auth\App\Http\Controllers\Admin\AdminSmsController::class, 'storeTemplate'])->name('admin.auth.sms.templates.store');
            Route::get('/templates/{id}/edit', [\Jiny\Auth\App\Http\Controllers\Admin\AdminSmsController::class, 'editTemplate'])->name('admin.auth.sms.templates.edit');
            Route::put('/templates/{id}', [\Jiny\Auth\App\Http\Controllers\Admin\AdminSmsController::class, 'updateTemplate'])->name('admin.auth.sms.templates.update');
            Route::delete('/templates/{id}', [\Jiny\Auth\App\Http\Controllers\Admin\AdminSmsController::class, 'destroyTemplate'])->name('admin.auth.sms.templates.destroy');
        });
        
        // 회원 등급 관리
        Route::prefix('auth/grades')->group(function () {
            Route::get('/', [\Jiny\Auth\App\Http\Controllers\Admin\AdminUserGradeController::class, 'index'])->name('admin.auth.grades');
            Route::get('/create', [\Jiny\Auth\App\Http\Controllers\Admin\AdminUserGradeController::class, 'create'])->name('admin.auth.grades.create');
            Route::post('/', [\Jiny\Auth\App\Http\Controllers\Admin\AdminUserGradeController::class, 'store'])->name('admin.auth.grades.store');
            Route::get('/{id}/edit', [\Jiny\Auth\App\Http\Controllers\Admin\AdminUserGradeController::class, 'edit'])->name('admin.auth.grades.edit');
            Route::put('/{id}', [\Jiny\Auth\App\Http\Controllers\Admin\AdminUserGradeController::class, 'update'])->name('admin.auth.grades.update');
            Route::delete('/{id}', [\Jiny\Auth\App\Http\Controllers\Admin\AdminUserGradeController::class, 'destroy'])->name('admin.auth.grades.destroy');
            Route::post('/users/{id}/grade', [\Jiny\Auth\App\Http\Controllers\Admin\AdminUserGradeController::class, 'changeUserGrade'])->name('admin.auth.users.grade.change');
            Route::get('/statistics', [\Jiny\Auth\App\Http\Controllers\Admin\AdminUserGradeController::class, 'statistics'])->name('admin.auth.grades.statistics');
            Route::post('/auto-upgrade', [\Jiny\Auth\App\Http\Controllers\Admin\AdminUserGradeController::class, 'processAutoUpgrade'])->name('admin.auth.grades.auto-upgrade');
        });
        
        // 회원 유형 관리
        Route::prefix('auth/user-types')->group(function () {
            Route::get('/', [\Jiny\Auth\App\Http\Controllers\Admin\AdminUserTypeController::class, 'index'])->name('admin.auth.user-types');
            Route::get('/create', [\Jiny\Auth\App\Http\Controllers\Admin\AdminUserTypeController::class, 'create'])->name('admin.auth.user-types.create');
            Route::post('/', [\Jiny\Auth\App\Http\Controllers\Admin\AdminUserTypeController::class, 'store'])->name('admin.auth.user-types.store');
            Route::get('/{id}/edit', [\Jiny\Auth\App\Http\Controllers\Admin\AdminUserTypeController::class, 'edit'])->name('admin.auth.user-types.edit');
            Route::put('/{id}', [\Jiny\Auth\App\Http\Controllers\Admin\AdminUserTypeController::class, 'update'])->name('admin.auth.user-types.update');
            Route::delete('/{id}', [\Jiny\Auth\App\Http\Controllers\Admin\AdminUserTypeController::class, 'destroy'])->name('admin.auth.user-types.destroy');
            Route::post('/users/{id}/type', [\Jiny\Auth\App\Http\Controllers\Admin\AdminUserTypeController::class, 'changeUserType'])->name('admin.auth.users.type.change');
            Route::get('/statistics', [\Jiny\Auth\App\Http\Controllers\Admin\AdminUserTypeController::class, 'statistics'])->name('admin.auth.user-types.statistics');
        });
        
        // 디바이스 관리
        Route::prefix('auth/devices')->group(function () {
            Route::get('/', [\Jiny\Auth\App\Http\Controllers\Admin\AdminDeviceController::class, 'index'])->name('admin.auth.devices');
            Route::get('/statistics', [\Jiny\Auth\App\Http\Controllers\Admin\AdminDeviceController::class, 'statistics'])->name('admin.auth.devices.statistics');
            Route::get('/{id}', [\Jiny\Auth\App\Http\Controllers\Admin\AdminDeviceController::class, 'show'])->name('admin.auth.devices.show');
            Route::post('/{id}/block', [\Jiny\Auth\App\Http\Controllers\Admin\AdminDeviceController::class, 'block'])->name('admin.auth.devices.block');
            Route::post('/{id}/unblock', [\Jiny\Auth\App\Http\Controllers\Admin\AdminDeviceController::class, 'unblock'])->name('admin.auth.devices.unblock');
            Route::post('/{id}/trust', [\Jiny\Auth\App\Http\Controllers\Admin\AdminDeviceController::class, 'trust'])->name('admin.auth.devices.trust');
            Route::post('/{id}/untrust', [\Jiny\Auth\App\Http\Controllers\Admin\AdminDeviceController::class, 'untrust'])->name('admin.auth.devices.untrust');
            Route::delete('/{id}', [\Jiny\Auth\App\Http\Controllers\Admin\AdminDeviceController::class, 'destroy'])->name('admin.auth.devices.destroy');
        });
        
        // 사용자별 디바이스 목록
        Route::get('/auth/users/{userId}/devices', [\Jiny\Auth\App\Http\Controllers\Admin\AdminDeviceController::class, 'userDevices'])->name('admin.auth.users.devices');
        
        // 포인트 관리
        Route::prefix('auth/points')->group(function () {
            Route::get('/', [\Jiny\Auth\App\Http\Controllers\Admin\AdminPointController::class, 'index'])->name('admin.auth.points');
            Route::post('/{userId}/add', [\Jiny\Auth\App\Http\Controllers\Admin\AdminPointController::class, 'add'])->name('admin.auth.points.add');
            Route::post('/{userId}/deduct', [\Jiny\Auth\App\Http\Controllers\Admin\AdminPointController::class, 'deduct'])->name('admin.auth.points.deduct');
            Route::get('/{userId}/history', [\Jiny\Auth\App\Http\Controllers\Admin\AdminPointController::class, 'history'])->name('admin.auth.points.history');
            Route::get('/statistics', [\Jiny\Auth\App\Http\Controllers\Admin\AdminPointController::class, 'statistics'])->name('admin.auth.points.statistics');
            Route::post('/process-expired', [\Jiny\Auth\App\Http\Controllers\Admin\AdminPointController::class, 'processExpiredPoints'])->name('admin.auth.points.process-expired');
        });
        
        // eMoney 관리
        Route::prefix('auth/emoney')->group(function () {
            Route::get('/', [\Jiny\Auth\App\Http\Controllers\Admin\AdminEmoneyController::class, 'index'])->name('admin.auth.emoney');
            Route::get('/user', [\Jiny\Auth\App\Http\Controllers\Admin\AdminEmoneyController::class, 'userList'])->name('admin.auth.emoney.user');
            Route::get('/log/{userId}', [\Jiny\Auth\App\Http\Controllers\Admin\AdminEmoneyController::class, 'userLog'])->name('admin.auth.emoney.log');
            Route::get('/bank/{userId}', [\Jiny\Auth\App\Http\Controllers\Admin\AdminEmoneyController::class, 'userBankAccounts'])->name('admin.auth.emoney.bank');
            Route::get('/withdraw/{id}', [\Jiny\Auth\App\Http\Controllers\Admin\AdminEmoneyController::class, 'withdrawalDetail'])->name('admin.auth.emoney.withdraw');
            Route::post('/withdraw/{id}/approve', [\Jiny\Auth\App\Http\Controllers\Admin\AdminEmoneyController::class, 'approveWithdrawal'])->name('admin.auth.emoney.withdraw.approve');
            Route::post('/withdraw/{id}/reject', [\Jiny\Auth\App\Http\Controllers\Admin\AdminEmoneyController::class, 'rejectWithdrawal'])->name('admin.auth.emoney.withdraw.reject');
            Route::get('/deposit/{id}', [\Jiny\Auth\App\Http\Controllers\Admin\AdminEmoneyController::class, 'depositDetail'])->name('admin.auth.emoney.deposit');
            Route::post('/deposit/{id}/confirm', [\Jiny\Auth\App\Http\Controllers\Admin\AdminEmoneyController::class, 'confirmDeposit'])->name('admin.auth.emoney.deposit.confirm');
        });
        
        // 은행 및 통화 관리
        Route::get('/auth/bank', [\Jiny\Auth\App\Http\Controllers\Admin\AdminEmoneyController::class, 'bankList'])->name('admin.auth.bank');
        Route::get('/auth/currency', [\Jiny\Auth\App\Http\Controllers\Admin\AdminEmoneyController::class, 'currencyList'])->name('admin.auth.currency');
        Route::get('/auth/currency/log/{code}', [\Jiny\Auth\App\Http\Controllers\Admin\AdminEmoneyController::class, 'currencyLog'])->name('admin.auth.currency.log');
        
        // 로그 내보내기
        Route::prefix('auth/export')->group(function () {
            Route::get('/login-history', [\Jiny\Auth\App\Http\Controllers\Admin\AdminLogExportController::class, 'exportLoginHistory'])->name('admin.auth.export.login-history');
            Route::get('/account-logs', [\Jiny\Auth\App\Http\Controllers\Admin\AdminLogExportController::class, 'exportAccountLogs'])->name('admin.auth.export.account-logs');
            Route::get('/security-logs', [\Jiny\Auth\App\Http\Controllers\Admin\AdminLogExportController::class, 'exportSecurityLogs'])->name('admin.auth.export.security-logs');
            Route::get('/permission-logs', [\Jiny\Auth\App\Http\Controllers\Admin\AdminLogExportController::class, 'permissionLogs'])->name('admin.auth.export.permission-logs');
        });
        
        // 사용자 통계
        Route::prefix('auth/statistics')->group(function () {
            Route::get('/registrations', [\Jiny\Auth\App\Http\Controllers\Admin\AdminStatisticsController::class, 'registrations'])->name('admin.auth.statistics.registrations');
            Route::get('/active-users', [\Jiny\Auth\App\Http\Controllers\Admin\AdminStatisticsController::class, 'activeUsers'])->name('admin.auth.statistics.active-users');
            Route::get('/login-patterns', [\Jiny\Auth\App\Http\Controllers\Admin\AdminStatisticsController::class, 'loginPatterns'])->name('admin.auth.statistics.login-patterns');
            Route::get('/retention', [\Jiny\Auth\App\Http\Controllers\Admin\AdminStatisticsController::class, 'retention'])->name('admin.auth.statistics.retention');
        });
        
        // 시스템 설정
        Route::prefix('auth/settings')->group(function () {
            // 로그인 설정
            Route::get('/login', [\Jiny\Auth\App\Http\Controllers\Admin\AdminAuthSettingsController::class, 'loginSettings'])->name('admin.auth.settings.login');
            Route::post('/login', [\Jiny\Auth\App\Http\Controllers\Admin\AdminAuthSettingsController::class, 'updateLoginSettings'])->name('admin.auth.settings.login.update');
            
            // 가입 설정
            Route::get('/registration', [\Jiny\Auth\App\Http\Controllers\Admin\AdminAuthSettingsController::class, 'registrationSettings'])->name('admin.auth.settings.registration');
            Route::post('/registration', [\Jiny\Auth\App\Http\Controllers\Admin\AdminAuthSettingsController::class, 'updateRegistrationSettings'])->name('admin.auth.settings.registration.update');
            
            // 보안 설정
            Route::get('/security', [\Jiny\Auth\App\Http\Controllers\Admin\AdminSecuritySettingsController::class, 'securitySettings'])->name('admin.auth.settings.security');
            Route::post('/security', [\Jiny\Auth\App\Http\Controllers\Admin\AdminSecuritySettingsController::class, 'updateSecuritySettings'])->name('admin.auth.settings.security.update');
            
            // CAPTCHA 설정
            Route::get('/captcha', [\Jiny\Auth\App\Http\Controllers\Admin\AdminSecuritySettingsController::class, 'captchaSettings'])->name('admin.auth.settings.captcha');
            Route::post('/captcha', [\Jiny\Auth\App\Http\Controllers\Admin\AdminSecuritySettingsController::class, 'updateCaptchaSettings'])->name('admin.auth.settings.captcha.update');
            
            // IP 화이트리스트
            Route::get('/whitelist', [\Jiny\Auth\App\Http\Controllers\Admin\AdminSecuritySettingsController::class, 'ipWhitelist'])->name('admin.auth.settings.whitelist');
            Route::post('/whitelist', [\Jiny\Auth\App\Http\Controllers\Admin\AdminSecuritySettingsController::class, 'addIpWhitelist'])->name('admin.auth.settings.whitelist.add');
            Route::delete('/whitelist/{id}', [\Jiny\Auth\App\Http\Controllers\Admin\AdminSecuritySettingsController::class, 'removeIpWhitelist'])->name('admin.auth.settings.whitelist.remove');
        });
        
        // 대량 작업
        Route::prefix('auth/bulk')->group(function () {
            Route::post('/activate', [\Jiny\Auth\App\Http\Controllers\Admin\AdminBulkController::class, 'activate'])->name('admin.auth.bulk.activate');
            Route::post('/deactivate', [\Jiny\Auth\App\Http\Controllers\Admin\AdminBulkController::class, 'deactivate'])->name('admin.auth.bulk.deactivate');
            Route::post('/delete', [\Jiny\Auth\App\Http\Controllers\Admin\AdminBulkController::class, 'delete'])->name('admin.auth.bulk.delete');
            Route::post('/export', [\Jiny\Auth\App\Http\Controllers\Admin\AdminBulkController::class, 'export'])->name('admin.auth.bulk.export');
            Route::post('/import', [\Jiny\Auth\App\Http\Controllers\Admin\AdminBulkController::class, 'import'])->name('admin.auth.bulk.import');
            Route::post('/send-email', [\Jiny\Auth\App\Http\Controllers\Admin\AdminBulkController::class, 'sendEmail'])->name('admin.auth.bulk.send-email');
            Route::post('/reset-password', [\Jiny\Auth\App\Http\Controllers\Admin\AdminBulkController::class, 'resetPassword'])->name('admin.auth.bulk.reset-password');
            Route::post('/change-grade', [\Jiny\Auth\App\Http\Controllers\Admin\AdminBulkController::class, 'changeGrade'])->name('admin.auth.bulk.change-grade');
            Route::post('/add-points', [\Jiny\Auth\App\Http\Controllers\Admin\AdminBulkController::class, 'addPoints'])->name('admin.auth.bulk.add-points');
        });
        
        // 긴급 상황 관리
        Route::prefix('auth/emergency')->group(function () {
            // 점검 모드
            Route::get('/maintenance', [\Jiny\Auth\App\Http\Controllers\Admin\AdminEmergencyController::class, 'maintenance'])->name('admin.auth.emergency.maintenance');
            Route::post('/maintenance', [\Jiny\Auth\App\Http\Controllers\Admin\AdminEmergencyController::class, 'toggleMaintenance'])->name('admin.auth.emergency.maintenance.toggle');
            
            // 로그인 차단
            Route::get('/block-login', [\Jiny\Auth\App\Http\Controllers\Admin\AdminEmergencyController::class, 'blockLogin'])->name('admin.auth.emergency.block-login');
            Route::post('/block-login', [\Jiny\Auth\App\Http\Controllers\Admin\AdminEmergencyController::class, 'toggleBlockLogin'])->name('admin.auth.emergency.block-login.toggle');
            
            // 긴급 알림
            Route::post('/alert', [\Jiny\Auth\App\Http\Controllers\Admin\AdminEmergencyController::class, 'sendAlert'])->name('admin.auth.emergency.alert');
            
            // 시스템 체크
            Route::get('/system-check', [\Jiny\Auth\App\Http\Controllers\Admin\AdminEmergencyController::class, 'systemCheck'])->name('admin.auth.emergency.system-check');
            
            // 세션 강제 종료
            Route::post('/kill-all-sessions', [\Jiny\Auth\App\Http\Controllers\Admin\AdminEmergencyController::class, 'killAllSessions'])->name('admin.auth.emergency.kill-sessions');
        });
        
        // 보안 사고 관리
        Route::prefix('auth/security-incident')->group(function () {
            Route::get('/', [\Jiny\Auth\App\Http\Controllers\Admin\AdminSecurityIncidentController::class, 'index'])->name('admin.auth.security-incident');
            Route::post('/', [\Jiny\Auth\App\Http\Controllers\Admin\AdminSecurityIncidentController::class, 'store'])->name('admin.auth.security-incident.store');
            Route::get('/{id}', [\Jiny\Auth\App\Http\Controllers\Admin\AdminSecurityIncidentController::class, 'show'])->name('admin.auth.security-incident.show');
            Route::put('/{id}', [\Jiny\Auth\App\Http\Controllers\Admin\AdminSecurityIncidentController::class, 'update'])->name('admin.auth.security-incident.update');
            Route::post('/{id}/resolve', [\Jiny\Auth\App\Http\Controllers\Admin\AdminSecurityIncidentController::class, 'resolve'])->name('admin.auth.security-incident.resolve');
            Route::post('/{id}/action', [\Jiny\Auth\App\Http\Controllers\Admin\AdminSecurityIncidentController::class, 'addAction'])->name('admin.auth.security-incident.action');
        });
    });
}