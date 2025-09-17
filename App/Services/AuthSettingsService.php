<?php

namespace Jiny\Auth\App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class AuthSettingsService
{
    /**
     * 설정 값 가져오기
     */
    public static function get(string $group, string $key, $default = null)
    {
        $cacheKey = "auth_settings.{$group}.{$key}";
        
        return Cache::remember($cacheKey, 3600, function () use ($group, $key, $default) {
            $setting = DB::table('auth_settings')
                ->where('group', $group)
                ->where('key', $key)
                ->first();
            
            if (!$setting) {
                return $default;
            }
            
            // 암호화된 값 복호화
            if ($setting->is_encrypted) {
                $setting->value = Crypt::decryptString($setting->value);
            }
            
            // 타입에 따라 변환
            return self::castValue($setting->value, $setting->type);
        });
    }
    
    /**
     * 설정 값 설정하기
     */
    public static function set(string $group, string $key, $value, string $type = 'text', bool $encrypt = false)
    {
        // JSON 타입인 경우 인코딩
        if ($type === 'json' && is_array($value)) {
            $value = json_encode($value);
        }
        
        // 암호화가 필요한 경우
        if ($encrypt && $value) {
            $value = Crypt::encryptString($value);
        }
        
        DB::table('auth_settings')->updateOrInsert(
            [
                'group' => $group,
                'key' => $key
            ],
            [
                'value' => $value,
                'type' => $type,
                'is_encrypted' => $encrypt,
                'updated_at' => now()
            ]
        );
        
        // 캐시 삭제
        Cache::forget("auth_settings.{$group}.{$key}");
        
        return true;
    }
    
    /**
     * 그룹별 모든 설정 가져오기
     */
    public static function getGroup(string $group)
    {
        $settings = DB::table('auth_settings')
            ->where('group', $group)
            ->get();
        
        $result = [];
        foreach ($settings as $setting) {
            $value = $setting->value;
            
            // 암호화된 값 복호화
            if ($setting->is_encrypted && $value) {
                try {
                    $value = Crypt::decryptString($value);
                } catch (\Exception $e) {
                    $value = '';
                }
            }
            
            $result[$setting->key] = [
                'value' => self::castValue($value, $setting->type),
                'type' => $setting->type,
                'description' => $setting->description,
                'is_encrypted' => $setting->is_encrypted
            ];
        }
        
        return $result;
    }
    
    /**
     * 그룹별 설정 업데이트
     */
    public static function updateGroup(string $group, array $settings)
    {
        foreach ($settings as $key => $value) {
            $existing = DB::table('auth_settings')
                ->where('group', $group)
                ->where('key', $key)
                ->first();
            
            if ($existing) {
                self::set($group, $key, $value, $existing->type, $existing->is_encrypted);
            }
        }
        
        return true;
    }
    
    /**
     * 값 타입 변환
     */
    private static function castValue($value, $type)
    {
        switch ($type) {
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'integer':
                return (int) $value;
            case 'json':
                return json_decode($value, true) ?: [];
            default:
                return $value;
        }
    }
    
    /**
     * 로그인 설정 가져오기
     */
    public static function getLoginSettings()
    {
        return [
            'enable_remember_me' => self::get('login', 'enable_remember_me', true),
            'max_attempts' => self::get('login', 'max_attempts', 5),
            'lockout_duration' => self::get('login', 'lockout_duration', 15),
            'session_lifetime' => self::get('login', 'session_lifetime', 120),
            'enable_2fa' => self::get('login', 'enable_2fa', false),
            'force_2fa_for_admin' => self::get('login', 'force_2fa_for_admin', false),
            'allow_multiple_sessions' => self::get('login', 'allow_multiple_sessions', true),
            'enable_device_tracking' => self::get('login', 'enable_device_tracking', true)
        ];
    }
    
    /**
     * 가입 설정 가져오기
     */
    public static function getRegistrationSettings()
    {
        return [
            'enable_registration' => self::get('registration', 'enable_registration', true),
            'require_email_verification' => self::get('registration', 'require_email_verification', true),
            'require_phone_verification' => self::get('registration', 'require_phone_verification', false),
            'require_terms_agreement' => self::get('registration', 'require_terms_agreement', true),
            'auto_approve' => self::get('registration', 'auto_approve', true),
            'default_user_type' => self::get('registration', 'default_user_type', 'general'),
            'default_user_grade' => self::get('registration', 'default_user_grade', 'bronze'),
            'welcome_point' => self::get('registration', 'welcome_point', 1000),
            'welcome_emoney' => self::get('registration', 'welcome_emoney', 0),
            'allowed_domains' => self::get('registration', 'allowed_domains', []),
            'blocked_domains' => self::get('registration', 'blocked_domains', [])
        ];
    }
    
    /**
     * 보안 설정 가져오기
     */
    public static function getSecuritySettings()
    {
        return [
            'password_min_length' => self::get('security', 'password_min_length', 8),
            'password_require_uppercase' => self::get('security', 'password_require_uppercase', true),
            'password_require_lowercase' => self::get('security', 'password_require_lowercase', true),
            'password_require_number' => self::get('security', 'password_require_number', true),
            'password_require_special' => self::get('security', 'password_require_special', false),
            'password_expiry_days' => self::get('security', 'password_expiry_days', 90),
            'password_history_count' => self::get('security', 'password_history_count', 3),
            'enable_ip_whitelist' => self::get('security', 'enable_ip_whitelist', false),
            'enable_geo_blocking' => self::get('security', 'enable_geo_blocking', false),
            'blocked_countries' => self::get('security', 'blocked_countries', []),
            'enable_brute_force_protection' => self::get('security', 'enable_brute_force_protection', true),
            'enable_suspicious_login_detection' => self::get('security', 'enable_suspicious_login_detection', true)
        ];
    }
    
    /**
     * CAPTCHA 설정 가져오기
     */
    public static function getCaptchaSettings()
    {
        return [
            'enable_captcha' => self::get('captcha', 'enable_captcha', false),
            'captcha_provider' => self::get('captcha', 'captcha_provider', 'recaptcha'),
            'captcha_on_login' => self::get('captcha', 'captcha_on_login', false),
            'captcha_on_registration' => self::get('captcha', 'captcha_on_registration', true),
            'captcha_on_password_reset' => self::get('captcha', 'captcha_on_password_reset', true),
            'captcha_after_failed_attempts' => self::get('captcha', 'captcha_after_failed_attempts', 3),
            'recaptcha_site_key' => self::get('captcha', 'recaptcha_site_key', ''),
            'recaptcha_secret_key' => self::get('captcha', 'recaptcha_secret_key', '')
        ];
    }
}