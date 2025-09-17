<?php

namespace Jiny\Auth\App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Jiny\Auth\App\Services\AuthSettingsService;
use Illuminate\Support\Facades\Cache;

class AdminSecuritySettingsController extends Controller
{
    /**
     * 보안 설정 페이지
     * GET /admin/auth/settings/security
     */
    public function securitySettings()
    {
        $settings = AuthSettingsService::getGroup('security');
        
        // 국가 목록 가져오기
        $countries = \DB::table('auth_countries')->pluck('name_ko', 'code');
        
        return view('jiny-auth::admin.settings.security', compact('settings', 'countries'));
    }
    
    /**
     * 보안 설정 업데이트
     * POST /admin/auth/settings/security
     */
    public function updateSecuritySettings(Request $request)
    {
        $request->validate([
            'password_min_length' => 'required|integer|min:6|max:32',
            'password_require_uppercase' => 'required|in:true,false',
            'password_require_lowercase' => 'required|in:true,false',
            'password_require_number' => 'required|in:true,false',
            'password_require_special' => 'required|in:true,false',
            'password_expiry_days' => 'required|integer|min:0|max:365',
            'password_history_count' => 'required|integer|min:0|max:10',
            'enable_ip_whitelist' => 'required|in:true,false',
            'enable_geo_blocking' => 'required|in:true,false',
            'blocked_countries' => 'nullable|array',
            'enable_brute_force_protection' => 'required|in:true,false',
            'enable_suspicious_login_detection' => 'required|in:true,false'
        ]);
        
        $settings = [
            'password_min_length' => $request->password_min_length,
            'password_require_uppercase' => $request->password_require_uppercase === 'true',
            'password_require_lowercase' => $request->password_require_lowercase === 'true',
            'password_require_number' => $request->password_require_number === 'true',
            'password_require_special' => $request->password_require_special === 'true',
            'password_expiry_days' => $request->password_expiry_days,
            'password_history_count' => $request->password_history_count,
            'enable_ip_whitelist' => $request->enable_ip_whitelist === 'true',
            'enable_geo_blocking' => $request->enable_geo_blocking === 'true',
            'blocked_countries' => $request->blocked_countries ?? [],
            'enable_brute_force_protection' => $request->enable_brute_force_protection === 'true',
            'enable_suspicious_login_detection' => $request->enable_suspicious_login_detection === 'true'
        ];
        
        AuthSettingsService::updateGroup('security', $settings);
        
        // 보안 설정 변경 알림 (중요)
        activity()
            ->causedBy(auth()->user())
            ->withProperties($settings)
            ->log('보안 설정이 변경되었습니다');
        
        // 관리자들에게 이메일 알림
        $this->notifySecurityChange($settings);
        
        return redirect()->route('admin.auth.settings.security')
            ->with('success', '보안 설정이 업데이트되었습니다.');
    }
    
    /**
     * CAPTCHA 설정 페이지
     * GET /admin/auth/settings/captcha
     */
    public function captchaSettings()
    {
        $settings = AuthSettingsService::getGroup('captcha');
        
        return view('jiny-auth::admin.settings.captcha', compact('settings'));
    }
    
    /**
     * CAPTCHA 설정 업데이트
     * POST /admin/auth/settings/captcha
     */
    public function updateCaptchaSettings(Request $request)
    {
        $request->validate([
            'enable_captcha' => 'required|in:true,false',
            'captcha_provider' => 'required|in:recaptcha,hcaptcha',
            'captcha_on_login' => 'required|in:true,false',
            'captcha_on_registration' => 'required|in:true,false',
            'captcha_on_password_reset' => 'required|in:true,false',
            'captcha_after_failed_attempts' => 'required|integer|min:0|max:10',
            'recaptcha_site_key' => 'nullable|string',
            'recaptcha_secret_key' => 'nullable|string'
        ]);
        
        // CAPTCHA가 활성화되었는데 키가 없는 경우 체크
        if ($request->enable_captcha === 'true' && $request->captcha_provider === 'recaptcha') {
            if (empty($request->recaptcha_site_key) || empty($request->recaptcha_secret_key)) {
                return redirect()->back()
                    ->withErrors(['recaptcha_site_key' => 'reCAPTCHA를 사용하려면 사이트 키와 비밀 키가 필요합니다.'])
                    ->withInput();
            }
        }
        
        $settings = [
            'enable_captcha' => $request->enable_captcha === 'true',
            'captcha_provider' => $request->captcha_provider,
            'captcha_on_login' => $request->captcha_on_login === 'true',
            'captcha_on_registration' => $request->captcha_on_registration === 'true',
            'captcha_on_password_reset' => $request->captcha_on_password_reset === 'true',
            'captcha_after_failed_attempts' => $request->captcha_after_failed_attempts
        ];
        
        // reCAPTCHA 키 저장 (암호화)
        if ($request->filled('recaptcha_site_key')) {
            AuthSettingsService::set('captcha', 'recaptcha_site_key', $request->recaptcha_site_key, 'text', true);
        }
        if ($request->filled('recaptcha_secret_key')) {
            AuthSettingsService::set('captcha', 'recaptcha_secret_key', $request->recaptcha_secret_key, 'text', true);
        }
        
        AuthSettingsService::updateGroup('captcha', $settings);
        
        // 설정 변경 로그
        activity()
            ->causedBy(auth()->user())
            ->withProperties($settings)
            ->log('CAPTCHA 설정이 변경되었습니다');
        
        return redirect()->route('admin.auth.settings.captcha')
            ->with('success', 'CAPTCHA 설정이 업데이트되었습니다.');
    }
    
    /**
     * IP 화이트리스트 관리
     * GET /admin/auth/settings/whitelist
     */
    public function ipWhitelist()
    {
        $whitelist = \DB::table('auth_ip_whitelist')->orderBy('created_at', 'desc')->paginate(20);
        
        return view('jiny-auth::admin.settings.ip-whitelist', compact('whitelist'));
    }
    
    /**
     * IP 화이트리스트 추가
     * POST /admin/auth/settings/whitelist
     */
    public function addIpWhitelist(Request $request)
    {
        $request->validate([
            'ip_address' => 'required|ip',
            'description' => 'nullable|string|max:255'
        ]);
        
        // 중복 체크
        $exists = \DB::table('auth_ip_whitelist')
            ->where('ip_address', $request->ip_address)
            ->exists();
        
        if ($exists) {
            return redirect()->back()->withErrors(['ip_address' => '이미 등록된 IP입니다.']);
        }
        
        \DB::table('auth_ip_whitelist')->insert([
            'ip_address' => $request->ip_address,
            'description' => $request->description,
            'added_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        activity()
            ->causedBy(auth()->user())
            ->withProperties(['ip_address' => $request->ip_address])
            ->log('IP 화이트리스트에 추가되었습니다');
        
        return redirect()->route('admin.auth.settings.whitelist')
            ->with('success', 'IP가 화이트리스트에 추가되었습니다.');
    }
    
    /**
     * IP 화이트리스트 삭제
     * DELETE /admin/auth/settings/whitelist/{id}
     */
    public function removeIpWhitelist($id)
    {
        $ip = \DB::table('auth_ip_whitelist')->find($id);
        
        if (!$ip) {
            return redirect()->route('admin.auth.settings.whitelist')
                ->withErrors(['error' => 'IP를 찾을 수 없습니다.']);
        }
        
        \DB::table('auth_ip_whitelist')->where('id', $id)->delete();
        
        activity()
            ->causedBy(auth()->user())
            ->withProperties(['ip_address' => $ip->ip_address])
            ->log('IP 화이트리스트에서 제거되었습니다');
        
        return redirect()->route('admin.auth.settings.whitelist')
            ->with('success', 'IP가 화이트리스트에서 제거되었습니다.');
    }
    
    /**
     * 보안 설정 변경 알림
     */
    private function notifySecurityChange($settings)
    {
        // 모든 관리자 가져오기
        $admins = \App\Models\User::where('is_admin', true)->get();
        
        foreach ($admins as $admin) {
            // 이메일 알림 발송
            \Mail::to($admin->email)->queue(new \Jiny\Auth\Mail\SecuritySettingsChanged($settings, auth()->user()));
        }
    }
}