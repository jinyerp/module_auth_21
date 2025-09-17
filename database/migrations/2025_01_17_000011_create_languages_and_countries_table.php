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
        // 언어 테이블
        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique(); // ko, en, ja, zh-CN 등
            $table->string('name'); // 한국어, English, 日本語 등
            $table->string('native_name'); // 원어 표기
            $table->string('direction', 3)->default('ltr'); // ltr, rtl
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->integer('sort_order')->default(0);
            $table->json('locale_settings')->nullable(); // 지역화 설정 (날짜 형식, 통화 등)
            $table->timestamps();
            
            $table->index('code');
            $table->index('is_active');
            $table->index('sort_order');
        });
        
        // 국가 테이블
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('code', 2)->unique(); // ISO 3166-1 alpha-2 (KR, US, JP 등)
            $table->string('code3', 3)->unique(); // ISO 3166-1 alpha-3 (KOR, USA, JPN 등)
            $table->string('numeric_code', 3); // ISO 3166-1 numeric
            $table->string('name'); // 영문 국가명
            $table->string('native_name'); // 현지어 국가명
            $table->string('capital')->nullable(); // 수도
            $table->string('region')->nullable(); // 지역 (Asia, Europe 등)
            $table->string('subregion')->nullable(); // 세부 지역
            $table->string('currency_code', 3)->nullable(); // 통화 코드
            $table->string('currency_name')->nullable(); // 통화 이름
            $table->string('currency_symbol')->nullable(); // 통화 심볼
            $table->string('phone_code')->nullable(); // 국가 전화 코드
            $table->string('timezone')->nullable(); // 대표 시간대
            $table->json('timezones')->nullable(); // 모든 시간대 목록
            $table->json('languages')->nullable(); // 사용 언어 목록
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('flag_emoji')->nullable(); // 국기 이모지
            $table->string('flag_url')->nullable(); // 국기 이미지 URL
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->index('code');
            $table->index('code3');
            $table->index('name');
            $table->index('is_active');
            $table->index('sort_order');
        });
        
        // 사용자 언어 설정
        Schema::create('user_language_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('language_id')->constrained()->cascadeOnDelete();
            $table->foreignId('country_id')->nullable()->constrained()->nullOnDelete();
            $table->string('timezone')->nullable();
            $table->string('date_format')->nullable();
            $table->string('time_format')->nullable();
            $table->string('number_format')->nullable();
            $table->string('currency_format')->nullable();
            $table->json('preferences')->nullable(); // 추가 환경 설정
            $table->timestamps();
            
            $table->unique('user_id');
            $table->index('language_id');
            $table->index('country_id');
        });
        
        // 브라우저 감지 로그
        Schema::create('browser_detections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('session_id')->nullable();
            $table->string('browser_name')->nullable();
            $table->string('browser_version')->nullable();
            $table->string('browser_engine')->nullable();
            $table->string('platform_name')->nullable();
            $table->string('platform_version')->nullable();
            $table->string('device_type')->nullable(); // desktop, mobile, tablet
            $table->string('device_brand')->nullable();
            $table->string('device_model')->nullable();
            $table->boolean('is_mobile')->default(false);
            $table->boolean('is_tablet')->default(false);
            $table->boolean('is_desktop')->default(false);
            $table->boolean('is_bot')->default(false);
            $table->string('detected_language')->nullable();
            $table->json('accept_languages')->nullable(); // Accept-Language 헤더
            $table->string('detected_timezone')->nullable();
            $table->integer('timezone_offset')->nullable(); // 분 단위 UTC 오프셋
            $table->string('detected_country')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->json('raw_data')->nullable(); // 원본 감지 데이터
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('session_id');
            $table->index('browser_name');
            $table->index('platform_name');
            $table->index('device_type');
            $table->index('detected_language');
            $table->index('detected_country');
            $table->index('created_at');
        });
        
        // 번역 문자열 (선택사항)
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->string('group');
            $table->string('key');
            $table->text('text')->nullable();
            $table->foreignId('language_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            
            $table->unique(['group', 'key', 'language_id']);
            $table->index('group');
            $table->index('key');
        });
        
        // 기본 언어 데이터 삽입
        $languages = [
            ['code' => 'ko', 'name' => 'Korean', 'native_name' => '한국어', 'direction' => 'ltr', 'is_active' => true, 'is_default' => true, 'sort_order' => 1],
            ['code' => 'en', 'name' => 'English', 'native_name' => 'English', 'direction' => 'ltr', 'is_active' => true, 'is_default' => false, 'sort_order' => 2],
            ['code' => 'ja', 'name' => 'Japanese', 'native_name' => '日本語', 'direction' => 'ltr', 'is_active' => true, 'is_default' => false, 'sort_order' => 3],
            ['code' => 'zh-CN', 'name' => 'Chinese (Simplified)', 'native_name' => '简体中文', 'direction' => 'ltr', 'is_active' => true, 'is_default' => false, 'sort_order' => 4],
            ['code' => 'zh-TW', 'name' => 'Chinese (Traditional)', 'native_name' => '繁體中文', 'direction' => 'ltr', 'is_active' => true, 'is_default' => false, 'sort_order' => 5],
            ['code' => 'es', 'name' => 'Spanish', 'native_name' => 'Español', 'direction' => 'ltr', 'is_active' => false, 'is_default' => false, 'sort_order' => 6],
            ['code' => 'fr', 'name' => 'French', 'native_name' => 'Français', 'direction' => 'ltr', 'is_active' => false, 'is_default' => false, 'sort_order' => 7],
            ['code' => 'de', 'name' => 'German', 'native_name' => 'Deutsch', 'direction' => 'ltr', 'is_active' => false, 'is_default' => false, 'sort_order' => 8],
            ['code' => 'ar', 'name' => 'Arabic', 'native_name' => 'العربية', 'direction' => 'rtl', 'is_active' => false, 'is_default' => false, 'sort_order' => 9],
        ];
        
        foreach ($languages as $lang) {
            DB::table('languages')->insert(array_merge($lang, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
        
        // 기본 국가 데이터 삽입 (주요 국가)
        $countries = [
            [
                'code' => 'KR', 'code3' => 'KOR', 'numeric_code' => '410',
                'name' => 'South Korea', 'native_name' => '대한민국',
                'capital' => 'Seoul', 'region' => 'Asia', 'subregion' => 'Eastern Asia',
                'currency_code' => 'KRW', 'currency_name' => 'South Korean won', 'currency_symbol' => '₩',
                'phone_code' => '+82', 'timezone' => 'Asia/Seoul',
                'timezones' => json_encode(['Asia/Seoul']),
                'languages' => json_encode(['ko']),
                'latitude' => 37.566535, 'longitude' => 126.977969,
                'flag_emoji' => '🇰🇷', 'is_active' => true, 'sort_order' => 1
            ],
            [
                'code' => 'US', 'code3' => 'USA', 'numeric_code' => '840',
                'name' => 'United States', 'native_name' => 'United States',
                'capital' => 'Washington D.C.', 'region' => 'Americas', 'subregion' => 'Northern America',
                'currency_code' => 'USD', 'currency_name' => 'United States dollar', 'currency_symbol' => '$',
                'phone_code' => '+1', 'timezone' => 'America/New_York',
                'timezones' => json_encode(['America/New_York', 'America/Chicago', 'America/Denver', 'America/Los_Angeles', 'America/Anchorage', 'Pacific/Honolulu']),
                'languages' => json_encode(['en']),
                'latitude' => 38.895111, 'longitude' => -77.036667,
                'flag_emoji' => '🇺🇸', 'is_active' => true, 'sort_order' => 2
            ],
            [
                'code' => 'JP', 'code3' => 'JPN', 'numeric_code' => '392',
                'name' => 'Japan', 'native_name' => '日本',
                'capital' => 'Tokyo', 'region' => 'Asia', 'subregion' => 'Eastern Asia',
                'currency_code' => 'JPY', 'currency_name' => 'Japanese yen', 'currency_symbol' => '¥',
                'phone_code' => '+81', 'timezone' => 'Asia/Tokyo',
                'timezones' => json_encode(['Asia/Tokyo']),
                'languages' => json_encode(['ja']),
                'latitude' => 35.689487, 'longitude' => 139.691706,
                'flag_emoji' => '🇯🇵', 'is_active' => true, 'sort_order' => 3
            ],
            [
                'code' => 'CN', 'code3' => 'CHN', 'numeric_code' => '156',
                'name' => 'China', 'native_name' => '中国',
                'capital' => 'Beijing', 'region' => 'Asia', 'subregion' => 'Eastern Asia',
                'currency_code' => 'CNY', 'currency_name' => 'Chinese yuan', 'currency_symbol' => '¥',
                'phone_code' => '+86', 'timezone' => 'Asia/Shanghai',
                'timezones' => json_encode(['Asia/Shanghai', 'Asia/Urumqi']),
                'languages' => json_encode(['zh-CN']),
                'latitude' => 39.904200, 'longitude' => 116.407396,
                'flag_emoji' => '🇨🇳', 'is_active' => true, 'sort_order' => 4
            ],
            [
                'code' => 'GB', 'code3' => 'GBR', 'numeric_code' => '826',
                'name' => 'United Kingdom', 'native_name' => 'United Kingdom',
                'capital' => 'London', 'region' => 'Europe', 'subregion' => 'Northern Europe',
                'currency_code' => 'GBP', 'currency_name' => 'British pound', 'currency_symbol' => '£',
                'phone_code' => '+44', 'timezone' => 'Europe/London',
                'timezones' => json_encode(['Europe/London']),
                'languages' => json_encode(['en']),
                'latitude' => 51.507351, 'longitude' => -0.127758,
                'flag_emoji' => '🇬🇧', 'is_active' => true, 'sort_order' => 5
            ],
        ];
        
        foreach ($countries as $country) {
            DB::table('countries')->insert(array_merge($country, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('translations');
        Schema::dropIfExists('browser_detections');
        Schema::dropIfExists('user_language_settings');
        Schema::dropIfExists('countries');
        Schema::dropIfExists('languages');
    }
};