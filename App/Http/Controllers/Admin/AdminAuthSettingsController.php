<?php

namespace Jiny\Auth\App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Jiny\Auth\App\Services\AuthSettingsService;
use Illuminate\Support\Facades\Cache;

class AdminAuthSettingsController extends Controller
{
    /**
     * 로그인 설정 페이지
     * GET /admin/auth/settings/login
     */
    public function loginSettings()
    {
        $settings = AuthSettingsService::getGroup('login');
        
        return view('jiny-auth::admin.settings.login', compact('settings'));
    }
    
    /**
     * 로그인 설정 업데이트
     * POST /admin/auth/settings/login
     */
    public function updateLoginSettings(Request $request)
    {
        $request->validate([
            'enable_remember_me' => 'required|in:true,false',
            'max_attempts' => 'required|integer|min:1|max:20',
            'lockout_duration' => 'required|integer|min:1|max:1440',
            'session_lifetime' => 'required|integer|min:10|max:10080',
            'enable_2fa' => 'required|in:true,false',
            'force_2fa_for_admin' => 'required|in:true,false',
            'allow_multiple_sessions' => 'required|in:true,false',
            'enable_device_tracking' => 'required|in:true,false'
        ]);
        
        $settings = [
            'enable_remember_me' => $request->enable_remember_me === 'true',
            'max_attempts' => $request->max_attempts,
            'lockout_duration' => $request->lockout_duration,
            'session_lifetime' => $request->session_lifetime,
            'enable_2fa' => $request->enable_2fa === 'true',
            'force_2fa_for_admin' => $request->force_2fa_for_admin === 'true',
            'allow_multiple_sessions' => $request->allow_multiple_sessions === 'true',
            'enable_device_tracking' => $request->enable_device_tracking === 'true'
        ];
        
        AuthSettingsService::updateGroup('login', $settings);
        
        // 설정 변경 로그
        activity()
            ->causedBy(auth()->user())
            ->withProperties($settings)
            ->log('로그인 설정이 변경되었습니다');
        
        return redirect()->route('admin.auth.settings.login')
            ->with('success', '로그인 설정이 업데이트되었습니다.');
    }
    
    /**
     * 가입 설정 페이지
     * GET /admin/auth/settings/registration
     */
    public function registrationSettings()
    {
        $settings = AuthSettingsService::getGroup('registration');
        
        // 사용자 유형과 등급 목록 가져오기
        $userTypes = \DB::table('auth_user_types')->pluck('name', 'code');
        $userGrades = \DB::table('auth_user_grades')->pluck('name', 'code');
        
        return view('jiny-auth::admin.settings.registration', compact('settings', 'userTypes', 'userGrades'));
    }
    
    /**
     * 가입 설정 업데이트
     * POST /admin/auth/settings/registration
     */
    public function updateRegistrationSettings(Request $request)
    {
        $request->validate([
            'enable_registration' => 'required|in:true,false',
            'require_email_verification' => 'required|in:true,false',
            'require_phone_verification' => 'required|in:true,false',
            'require_terms_agreement' => 'required|in:true,false',
            'auto_approve' => 'required|in:true,false',
            'default_user_type' => 'required|string',
            'default_user_grade' => 'required|string',
            'welcome_point' => 'required|integer|min:0',
            'welcome_emoney' => 'required|integer|min:0',
            'allowed_domains' => 'nullable|string',
            'blocked_domains' => 'nullable|string'
        ]);
        
        // 도메인 목록 파싱
        $allowedDomains = array_filter(array_map('trim', explode(',', $request->allowed_domains ?? '')));
        $blockedDomains = array_filter(array_map('trim', explode(',', $request->blocked_domains ?? '')));
        
        $settings = [
            'enable_registration' => $request->enable_registration === 'true',
            'require_email_verification' => $request->require_email_verification === 'true',
            'require_phone_verification' => $request->require_phone_verification === 'true',
            'require_terms_agreement' => $request->require_terms_agreement === 'true',
            'auto_approve' => $request->auto_approve === 'true',
            'default_user_type' => $request->default_user_type,
            'default_user_grade' => $request->default_user_grade,
            'welcome_point' => $request->welcome_point,
            'welcome_emoney' => $request->welcome_emoney,
            'allowed_domains' => $allowedDomains,
            'blocked_domains' => $blockedDomains
        ];
        
        AuthSettingsService::updateGroup('registration', $settings);
        
        // 설정 변경 로그
        activity()
            ->causedBy(auth()->user())
            ->withProperties($settings)
            ->log('회원가입 설정이 변경되었습니다');
        
        return redirect()->route('admin.auth.settings.registration')
            ->with('success', '회원가입 설정이 업데이트되었습니다.');
    }
}