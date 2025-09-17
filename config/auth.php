<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Features
    |--------------------------------------------------------------------------
    |
    | 사용할 인증 기능을 활성화/비활성화합니다.
    |
    */

    'features' => [
        'registration' => env('AUTH_REGISTRATION', true),
        'password_reset' => env('AUTH_PASSWORD_RESET', true),
        'email_verification' => env('AUTH_EMAIL_VERIFICATION', true),
        'two_factor_auth' => env('AUTH_TWO_FACTOR', false),
        'social_login' => env('AUTH_SOCIAL_LOGIN', false),
        'remember_me' => env('AUTH_REMEMBER_ME', true),
        'account_deletion' => env('AUTH_ACCOUNT_DELETION', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Policies
    |--------------------------------------------------------------------------
    |
    | 비밀번호 정책 설정
    |
    */

    'password' => [
        'min_length' => env('PASSWORD_MIN_LENGTH', 8),
        'max_length' => env('PASSWORD_MAX_LENGTH', 255),
        'require_uppercase' => env('PASSWORD_REQUIRE_UPPERCASE', true),
        'require_lowercase' => env('PASSWORD_REQUIRE_LOWERCASE', true),
        'require_numeric' => env('PASSWORD_REQUIRE_NUMERIC', true),
        'require_special_char' => env('PASSWORD_REQUIRE_SPECIAL', true),
        'expiration_days' => env('PASSWORD_EXPIRATION_DAYS', 90),
        'history_count' => env('PASSWORD_HISTORY_COUNT', 5),
        'reset_token_expiration' => env('PASSWORD_RESET_TOKEN_EXPIRATION', 60), // minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | Login Settings
    |--------------------------------------------------------------------------
    |
    | 로그인 관련 설정
    |
    */

    'login' => [
        'max_attempts' => env('LOGIN_MAX_ATTEMPTS', 5),
        'lockout_duration' => env('LOGIN_LOCKOUT_DURATION', 60), // minutes
        'remember_me_duration' => env('LOGIN_REMEMBER_DURATION', 30), // days
        'username_field' => env('LOGIN_USERNAME_FIELD', 'email'), // email, username, or both
        'case_sensitive' => env('LOGIN_CASE_SENSITIVE', false),
        'track_login_attempts' => env('LOGIN_TRACK_ATTEMPTS', true),
        'track_login_history' => env('LOGIN_TRACK_HISTORY', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Registration Settings
    |--------------------------------------------------------------------------
    |
    | 회원가입 관련 설정
    |
    */

    'registration' => [
        'auto_login' => env('REGISTRATION_AUTO_LOGIN', false),
        'require_email_verification' => env('REGISTRATION_REQUIRE_EMAIL_VERIFICATION', true),
        'require_admin_approval' => env('REGISTRATION_REQUIRE_ADMIN_APPROVAL', false),
        'default_role' => env('REGISTRATION_DEFAULT_ROLE', 'user'),
        'allowed_domains' => env('REGISTRATION_ALLOWED_DOMAINS', null), // comma separated
        'blocked_domains' => env('REGISTRATION_BLOCKED_DOMAINS', null), // comma separated
        'username_min_length' => env('REGISTRATION_USERNAME_MIN_LENGTH', 3),
        'username_max_length' => env('REGISTRATION_USERNAME_MAX_LENGTH', 20),
    ],

    /*
    |--------------------------------------------------------------------------
    | Session Settings
    |--------------------------------------------------------------------------
    |
    | 세션 관련 설정
    |
    */

    'session' => [
        'lifetime' => env('SESSION_LIFETIME', 120), // minutes
        'expire_on_close' => env('SESSION_EXPIRE_ON_CLOSE', false),
        'encrypt' => env('SESSION_ENCRYPT', false),
        'single_session' => env('SESSION_SINGLE', false), // only one session per user
        'track_ip' => env('SESSION_TRACK_IP', true),
        'track_user_agent' => env('SESSION_TRACK_USER_AGENT', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Two Factor Authentication
    |--------------------------------------------------------------------------
    |
    | 2단계 인증 설정
    |
    */

    '2fa' => [
        'enabled' => env('2FA_ENABLED', false),
        'issuer' => env('2FA_ISSUER', config('app.name')),
        'recovery_codes' => env('2FA_RECOVERY_CODES', 8),
        'qr_code_size' => env('2FA_QR_CODE_SIZE', 200),
        'window' => env('2FA_WINDOW', 1), // time window for TOTP
        'force_for_roles' => [], // roles that must use 2FA
    ],

    /*
    |--------------------------------------------------------------------------
    | Social Authentication Providers
    |--------------------------------------------------------------------------
    |
    | 소셜 로그인 제공자 설정
    |
    */

    'social' => [
        'providers' => [
            'google' => [
                'enabled' => env('GOOGLE_AUTH_ENABLED', false),
                'client_id' => env('GOOGLE_CLIENT_ID'),
                'client_secret' => env('GOOGLE_CLIENT_SECRET'),
                'redirect' => env('GOOGLE_REDIRECT_URI'),
            ],
            'facebook' => [
                'enabled' => env('FACEBOOK_AUTH_ENABLED', false),
                'client_id' => env('FACEBOOK_CLIENT_ID'),
                'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
                'redirect' => env('FACEBOOK_REDIRECT_URI'),
            ],
            'github' => [
                'enabled' => env('GITHUB_AUTH_ENABLED', false),
                'client_id' => env('GITHUB_CLIENT_ID'),
                'client_secret' => env('GITHUB_CLIENT_SECRET'),
                'redirect' => env('GITHUB_REDIRECT_URI'),
            ],
            'kakao' => [
                'enabled' => env('KAKAO_AUTH_ENABLED', false),
                'client_id' => env('KAKAO_CLIENT_ID'),
                'client_secret' => env('KAKAO_CLIENT_SECRET'),
                'redirect' => env('KAKAO_REDIRECT_URI'),
            ],
            'naver' => [
                'enabled' => env('NAVER_AUTH_ENABLED', false),
                'client_id' => env('NAVER_CLIENT_ID'),
                'client_secret' => env('NAVER_CLIENT_SECRET'),
                'redirect' => env('NAVER_REDIRECT_URI'),
            ],
        ],
        'auto_create_user' => env('SOCIAL_AUTO_CREATE_USER', true),
        'auto_link_user' => env('SOCIAL_AUTO_LINK_USER', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Email Settings
    |--------------------------------------------------------------------------
    |
    | 이메일 관련 설정
    |
    */

    'email' => [
        'verification_expiration' => env('EMAIL_VERIFICATION_EXPIRATION', 60), // minutes
        'from_address' => env('MAIL_FROM_ADDRESS', 'noreply@example.com'),
        'from_name' => env('MAIL_FROM_NAME', config('app.name')),
        'queue_emails' => env('EMAIL_QUEUE', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | User Model Settings
    |--------------------------------------------------------------------------
    |
    | 사용자 모델 관련 설정
    |
    */

    'user' => [
        'model' => env('AUTH_USER_MODEL', 'App\\Models\\User'),
        'table' => env('AUTH_USER_TABLE', 'users'),
        'soft_delete' => env('AUTH_USER_SOFT_DELETE', true),
        'profile_photo' => env('AUTH_USER_PROFILE_PHOTO', true),
        'profile_photo_disk' => env('AUTH_USER_PROFILE_PHOTO_DISK', 'public'),
        'profile_photo_path' => env('AUTH_USER_PROFILE_PHOTO_PATH', 'profile-photos'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | API 레이트 제한 설정
    |
    */

    'rate_limiting' => [
        'login' => env('RATE_LIMIT_LOGIN', '5,1'), // attempts,minutes
        'register' => env('RATE_LIMIT_REGISTER', '3,10'),
        'password_reset' => env('RATE_LIMIT_PASSWORD_RESET', '3,10'),
        'email_verification' => env('RATE_LIMIT_EMAIL_VERIFICATION', '3,10'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Redirects
    |--------------------------------------------------------------------------
    |
    | 리다이렉트 경로 설정
    |
    */

    'redirects' => [
        'login' => env('LOGIN_REDIRECT', '/dashboard'),
        'logout' => env('LOGOUT_REDIRECT', '/'),
        'register' => env('REGISTER_REDIRECT', '/dashboard'),
        'password_reset' => env('PASSWORD_RESET_REDIRECT', '/dashboard'),
        'email_verification' => env('EMAIL_VERIFICATION_REDIRECT', '/dashboard'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Routes
    |--------------------------------------------------------------------------
    |
    | 라우트 설정
    |
    */

    'routes' => [
        'enabled' => env('AUTH_ROUTES_ENABLED', true),
        'prefix' => env('AUTH_ROUTES_PREFIX', ''),
        'middleware' => ['web'],
    ],

];