<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 인증 설정 테이블
        Schema::create('auth_settings', function (Blueprint $table) {
            $table->id();
            $table->string('group')->index(); // 설정 그룹 (login, registration, security, captcha 등)
            $table->string('key')->index(); // 설정 키
            $table->text('value')->nullable(); // 설정 값 (JSON 형식 가능)
            $table->string('type')->default('text'); // 값 타입 (text, boolean, integer, json)
            $table->text('description')->nullable(); // 설정 설명
            $table->boolean('is_encrypted')->default(false); // 암호화 여부
            $table->timestamps();
            
            $table->unique(['group', 'key']);
        });
        
        // 기본 로그인 설정 추가
        DB::table('auth_settings')->insert([
            [
                'group' => 'login',
                'key' => 'enable_remember_me',
                'value' => 'true',
                'type' => 'boolean',
                'description' => '로그인 유지 기능 사용 여부',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'group' => 'login',
                'key' => 'max_attempts',
                'value' => '5',
                'type' => 'integer',
                'description' => '최대 로그인 시도 횟수',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'group' => 'login',
                'key' => 'lockout_duration',
                'value' => '15',
                'type' => 'integer',
                'description' => '계정 잠금 시간 (분)',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'group' => 'login',
                'key' => 'session_lifetime',
                'value' => '120',
                'type' => 'integer',
                'description' => '세션 유효 시간 (분)',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'group' => 'login',
                'key' => 'enable_2fa',
                'value' => 'false',
                'type' => 'boolean',
                'description' => '2단계 인증 전역 사용 여부',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'group' => 'login',
                'key' => 'force_2fa_for_admin',
                'value' => 'false',
                'type' => 'boolean',
                'description' => '관리자 2FA 강제 사용',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'group' => 'login',
                'key' => 'allow_multiple_sessions',
                'value' => 'true',
                'type' => 'boolean',
                'description' => '다중 세션 허용 여부',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'group' => 'login',
                'key' => 'enable_device_tracking',
                'value' => 'true',
                'type' => 'boolean',
                'description' => '디바이스 추적 사용 여부',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
        
        // 기본 가입 설정 추가
        DB::table('auth_settings')->insert([
            [
                'group' => 'registration',
                'key' => 'enable_registration',
                'value' => 'true',
                'type' => 'boolean',
                'description' => '회원가입 허용 여부',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'group' => 'registration',
                'key' => 'require_email_verification',
                'value' => 'true',
                'type' => 'boolean',
                'description' => '이메일 인증 필수 여부',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'group' => 'registration',
                'key' => 'require_phone_verification',
                'value' => 'false',
                'type' => 'boolean',
                'description' => '휴대폰 인증 필수 여부',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'group' => 'registration',
                'key' => 'require_terms_agreement',
                'value' => 'true',
                'type' => 'boolean',
                'description' => '약관 동의 필수 여부',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'group' => 'registration',
                'key' => 'auto_approve',
                'value' => 'true',
                'type' => 'boolean',
                'description' => '자동 가입 승인 여부',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'group' => 'registration',
                'key' => 'default_user_type',
                'value' => 'general',
                'type' => 'text',
                'description' => '기본 사용자 유형',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'group' => 'registration',
                'key' => 'default_user_grade',
                'value' => 'bronze',
                'type' => 'text',
                'description' => '기본 사용자 등급',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'group' => 'registration',
                'key' => 'welcome_point',
                'value' => '1000',
                'type' => 'integer',
                'description' => '가입 축하 포인트',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'group' => 'registration',
                'key' => 'welcome_emoney',
                'value' => '0',
                'type' => 'integer',
                'description' => '가입 축하 eMoney',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'group' => 'registration',
                'key' => 'allowed_domains',
                'value' => json_encode([]),
                'type' => 'json',
                'description' => '허용된 이메일 도메인 (빈 배열은 모두 허용)',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'group' => 'registration',
                'key' => 'blocked_domains',
                'value' => json_encode(['mailinator.com', 'temp-mail.org']),
                'type' => 'json',
                'description' => '차단된 이메일 도메인',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
        
        // 기본 보안 설정 추가
        DB::table('auth_settings')->insert([
            [
                'group' => 'security',
                'key' => 'password_min_length',
                'value' => '8',
                'type' => 'integer',
                'description' => '비밀번호 최소 길이',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'group' => 'security',
                'key' => 'password_require_uppercase',
                'value' => 'true',
                'type' => 'boolean',
                'description' => '대문자 포함 필수',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'group' => 'security',
                'key' => 'password_require_lowercase',
                'value' => 'true',
                'type' => 'boolean',
                'description' => '소문자 포함 필수',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'group' => 'security',
                'key' => 'password_require_number',
                'value' => 'true',
                'type' => 'boolean',
                'description' => '숫자 포함 필수',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'group' => 'security',
                'key' => 'password_require_special',
                'value' => 'false',
                'type' => 'boolean',
                'description' => '특수문자 포함 필수',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'group' => 'security',
                'key' => 'password_expiry_days',
                'value' => '90',
                'type' => 'integer',
                'description' => '비밀번호 만료 기간 (일)',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'group' => 'security',
                'key' => 'password_history_count',
                'value' => '3',
                'type' => 'integer',
                'description' => '이전 비밀번호 재사용 금지 개수',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'group' => 'security',
                'key' => 'enable_ip_whitelist',
                'value' => 'false',
                'type' => 'boolean',
                'description' => 'IP 화이트리스트 사용 여부',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'group' => 'security',
                'key' => 'enable_geo_blocking',
                'value' => 'false',
                'type' => 'boolean',
                'description' => '지역 차단 사용 여부',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'group' => 'security',
                'key' => 'blocked_countries',
                'value' => json_encode([]),
                'type' => 'json',
                'description' => '차단된 국가 코드 목록',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'group' => 'security',
                'key' => 'enable_brute_force_protection',
                'value' => 'true',
                'type' => 'boolean',
                'description' => '무차별 대입 공격 방어 사용',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'group' => 'security',
                'key' => 'enable_suspicious_login_detection',
                'value' => 'true',
                'type' => 'boolean',
                'description' => '의심스러운 로그인 감지 사용',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
        
        // 기본 CAPTCHA 설정 추가
        DB::table('auth_settings')->insert([
            [
                'group' => 'captcha',
                'key' => 'enable_captcha',
                'value' => 'false',
                'type' => 'boolean',
                'description' => 'CAPTCHA 사용 여부',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'group' => 'captcha',
                'key' => 'captcha_provider',
                'value' => 'recaptcha',
                'type' => 'text',
                'description' => 'CAPTCHA 제공자 (recaptcha, hcaptcha)',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'group' => 'captcha',
                'key' => 'captcha_on_login',
                'value' => 'false',
                'type' => 'boolean',
                'description' => '로그인 시 CAPTCHA 사용',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'group' => 'captcha',
                'key' => 'captcha_on_registration',
                'value' => 'true',
                'type' => 'boolean',
                'description' => '회원가입 시 CAPTCHA 사용',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'group' => 'captcha',
                'key' => 'captcha_on_password_reset',
                'value' => 'true',
                'type' => 'boolean',
                'description' => '비밀번호 재설정 시 CAPTCHA 사용',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'group' => 'captcha',
                'key' => 'captcha_after_failed_attempts',
                'value' => '3',
                'type' => 'integer',
                'description' => '실패 횟수 후 CAPTCHA 표시',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'group' => 'captcha',
                'key' => 'recaptcha_site_key',
                'value' => '',
                'type' => 'text',
                'description' => 'reCAPTCHA 사이트 키',
                'is_encrypted' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'group' => 'captcha',
                'key' => 'recaptcha_secret_key',
                'value' => '',
                'type' => 'text',
                'description' => 'reCAPTCHA 비밀 키',
                'is_encrypted' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auth_settings');
    }
};