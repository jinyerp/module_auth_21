# 5단계: 다국어 지원 및 지역 설정

## 📋 개요
Laravel의 다국어 지원 기능을 활용하여 인증 시스템의 모든 메시지, UI 요소, 이메일 템플릿을 다국어로 제공하고, 지역별 설정(시간대, 통화, 날짜 형식)을 관리합니다.

## 🎯 주요 기능

### 5.1 언어 설정
- 사용자별 선호 언어 설정
- 자동 언어 감지 (브라우저/IP 기반)
- 언어 전환 UI
- RTL(Right-to-Left) 언어 지원

### 5.2 지역 설정
- 시간대(Timezone) 관리
- 날짜/시간 형식 설정
- 통화 및 숫자 형식
- 지역별 전화번호 형식

### 5.3 번역 관리
- 언어 파일 관리
- 동적 번역 콘텐츠
- 번역 누락 감지
- 번역 캐싱

### 5.4 다국어 콘텐츠
- 다국어 이메일 템플릿
- 다국어 알림 메시지
- 다국어 검증 메시지
- 다국어 도움말

## 🔗 라우트 (Routes)

### 언어 설정
| HTTP 메소드 | URI | 컨트롤러 메소드 | 설명 |
|------------|-----|----------------|------|
| GET | `/locale/{locale}` | LocaleController::setLocale | 언어 변경 |
| POST | `/user/preferences/language` | UserPreferencesController::updateLanguage | 사용자 언어 설정 |
| GET | `/user/preferences` | UserPreferencesController::index | 사용자 설정 페이지 |

### 관리자 번역 관리
| HTTP 메소드 | URI | 컨트롤러 메소드 | 설명 |
|------------|-----|----------------|------|
| GET | `/admin/auth/translations` | AdminTranslationController::index | 번역 관리 |
| GET | `/admin/auth/translations/{locale}` | AdminTranslationController::show | 언어별 번역 |
| POST | `/admin/auth/translations/{locale}` | AdminTranslationController::update | 번역 수정 |
| POST | `/admin/auth/translations/import` | AdminTranslationController::import | 번역 가져오기 |
| GET | `/admin/auth/translations/export/{locale}` | AdminTranslationController::export | 번역 내보내기 |
| GET | `/admin/auth/translations/missing` | AdminTranslationController::missing | 누락 번역 |

### 지역 설정 관리
| HTTP 메소드 | URI | 컨트롤러 메소드 | 설명 |
|------------|-----|----------------|------|
| GET | `/admin/auth/locales` | AdminLocaleController::index | 지원 언어 목록 |
| POST | `/admin/auth/locales` | AdminLocaleController::store | 언어 추가 |
| PUT | `/admin/auth/locales/{locale}` | AdminLocaleController::update | 언어 설정 수정 |
| DELETE | `/admin/auth/locales/{locale}` | AdminLocaleController::destroy | 언어 삭제 |
| POST | `/admin/auth/locales/{locale}/toggle` | AdminLocaleController::toggle | 언어 활성화/비활성화 |

## 🎮 컨트롤러

### LocaleController
**위치**: `/jiny/auth/App/Http/Controllers/LocaleController.php`

#### setLocale($locale)
```php
// 처리 로직
1. 지원 언어 확인
2. 세션에 언어 저장
3. 사용자 설정 업데이트 (로그인 시)
4. 쿠키 설정 (30일)
5. 이전 페이지로 리다이렉트
```

### AdminTranslationController
**위치**: `/jiny/auth/App/Http/Controllers/Admin/AdminTranslationController.php`

#### update(Request $request, $locale)
```php
// 요청 파라미터
'translations' => ['required', 'array'],
'translations.*.key' => ['required', 'string'],
'translations.*.value' => ['required', 'string']

// 처리
1. 언어 파일 업데이트
2. 캐시 삭제
3. 변경 로그 기록
```

## 💾 데이터베이스 테이블

### user_preferences
| 컬럼명 | 타입 | 설명 |
|--------|------|------|
| id | BIGINT | Primary Key |
| user_id | BIGINT | 사용자 ID |
| language | VARCHAR | 선호 언어 (ko, en, ja 등) |
| timezone | VARCHAR | 시간대 (Asia/Seoul 등) |
| date_format | VARCHAR | 날짜 형식 |
| time_format | VARCHAR | 시간 형식 |
| currency | VARCHAR | 통화 (KRW, USD 등) |
| number_format | VARCHAR | 숫자 형식 |

### auth_translations
| 컬럼명 | 타입 | 설명 |
|--------|------|------|
| id | BIGINT | Primary Key |
| locale | VARCHAR | 언어 코드 |
| group | VARCHAR | 번역 그룹 |
| key | VARCHAR | 번역 키 |
| value | TEXT | 번역 값 |
| created_by | BIGINT | 생성자 |
| updated_by | BIGINT | 수정자 |

### auth_supported_locales
| 컬럼명 | 타입 | 설명 |
|--------|------|------|
| id | BIGINT | Primary Key |
| code | VARCHAR | 언어 코드 (ko, en 등) |
| name | VARCHAR | 언어명 (한국어, English 등) |
| native_name | VARCHAR | 원어명 |
| direction | ENUM | ltr/rtl |
| is_active | BOOLEAN | 활성화 여부 |
| is_default | BOOLEAN | 기본 언어 |

## 🌍 언어 파일 구조

### 디렉토리 구조
```
/jiny/auth/resources/lang/
├── ko/
│   ├── auth.php
│   ├── validation.php
│   ├── messages.php
│   └── emails.php
├── en/
│   ├── auth.php
│   ├── validation.php
│   ├── messages.php
│   └── emails.php
└── ja/
    ├── auth.php
    ├── validation.php
    ├── messages.php
    └── emails.php
```

### auth.php 예시 (한국어)
```php
return [
    'failed' => '인증 정보가 일치하지 않습니다.',
    'password' => '비밀번호가 올바르지 않습니다.',
    'throttle' => '로그인 시도가 너무 많습니다. :seconds초 후에 다시 시도하세요.',
    'login' => [
        'title' => '로그인',
        'email' => '이메일',
        'password' => '비밀번호',
        'remember' => '자동 로그인',
        'forgot' => '비밀번호를 잊으셨나요?',
        'submit' => '로그인',
        'register' => '회원가입'
    ],
    'register' => [
        'title' => '회원가입',
        'name' => '이름',
        'email' => '이메일',
        'password' => '비밀번호',
        'confirm' => '비밀번호 확인',
        'terms' => '이용약관에 동의합니다',
        'submit' => '가입하기'
    ]
];
```

### emails.php 예시 (영어)
```php
return [
    'verification' => [
        'subject' => 'Verify Your Email Address',
        'greeting' => 'Hello :name!',
        'line1' => 'Please click the button below to verify your email address.',
        'action' => 'Verify Email',
        'line2' => 'If you did not create an account, no further action is required.'
    ],
    'password_reset' => [
        'subject' => 'Reset Password Notification',
        'greeting' => 'Hello!',
        'line1' => 'You are receiving this email because we received a password reset request.',
        'action' => 'Reset Password',
        'line2' => 'This password reset link will expire in :count minutes.',
        'line3' => 'If you did not request a password reset, no further action is required.'
    ]
];
```

## 🛡 미들웨어

### SetLocale
언어 설정 적용
```php
public function handle($request, Closure $next)
{
    // 우선순위: URL > 세션 > 쿠키 > 브라우저 > 기본값
    $locale = $request->segment(1);
    
    if (!in_array($locale, config('app.locales'))) {
        $locale = session('locale', 
            $request->cookie('locale', 
                $this->detectBrowserLocale($request)
            )
        );
    }
    
    app()->setLocale($locale);
    
    return $next($request);
}
```

### LocalizeRoutes
라우트 다국어 지원
```php
Route::group([
    'prefix' => '{locale?}',
    'middleware' => 'locale'
], function () {
    Route::get('/', 'HomeController@index');
    // 다국어 지원이 필요한 라우트들
});
```

## ⚙️ 설정

### 앱 설정
```php
// config/app.php
'locale' => env('APP_LOCALE', 'ko'),
'fallback_locale' => 'en',
'locales' => ['ko', 'en', 'ja', 'zh', 'es', 'fr'],
'rtl_locales' => ['ar', 'he', 'fa'],
```

### 지역 설정
```php
// config/locales.php
return [
    'ko' => [
        'name' => 'Korean',
        'native' => '한국어',
        'timezone' => 'Asia/Seoul',
        'date_format' => 'Y년 m월 d일',
        'time_format' => 'H:i',
        'currency' => 'KRW',
        'number_format' => [
            'decimals' => 0,
            'decimal_separator' => '.',
            'thousands_separator' => ','
        ]
    ],
    'en' => [
        'name' => 'English',
        'native' => 'English',
        'timezone' => 'UTC',
        'date_format' => 'm/d/Y',
        'time_format' => 'g:i A',
        'currency' => 'USD',
        'number_format' => [
            'decimals' => 2,
            'decimal_separator' => '.',
            'thousands_separator' => ','
        ]
    ]
];
```

## 📦 Request/Response 예시

### 언어 변경
```http
GET /locale/en
```

응답: 302 Redirect with Cookie

### 사용자 설정 업데이트
```http
POST /user/preferences/language
Content-Type: application/json

{
    "language": "ko",
    "timezone": "Asia/Seoul",
    "date_format": "Y-m-d",
    "currency": "KRW"
}
```

### 번역 업데이트
```http
POST /admin/auth/translations/ko
Content-Type: application/json

{
    "translations": [
        {
            "key": "auth.login.title",
            "value": "로그인"
        },
        {
            "key": "auth.login.submit",
            "value": "접속하기"
        }
    ]
}
```

## 🎨 다국어 뷰 구현

### Blade 템플릿에서 번역 사용
```blade
{{-- 기본 번역 --}}
<h1>{{ __('auth.login.title') }}</h1>

{{-- 파라미터 포함 --}}
<p>{{ __('auth.throttle', ['seconds' => 60]) }}</p>

{{-- 복수형 처리 --}}
<p>{{ trans_choice('messages.apples', $count) }}</p>

{{-- 조건부 번역 --}}
@lang('auth.login.forgot')
```

### JavaScript에서 번역 사용
```javascript
// 번역 데이터 전달
window.translations = @json(__('auth'));

// Vue.js 컴포넌트
export default {
    methods: {
        __(key, replace = {}) {
            let translation = key.split('.').reduce((t, k) => t[k], window.translations);
            
            Object.keys(replace).forEach(key => {
                translation = translation.replace(':' + key, replace[key]);
            });
            
            return translation;
        }
    }
}

// 사용 예시
this.__('login.title')
this.__('throttle', { seconds: 60 })
```

## 🔄 자동 언어 감지

### IP 기반 감지
```php
use GeoIP;

public function detectLocaleByIP($ip)
{
    $location = GeoIP::getLocation($ip);
    
    $countryLocales = [
        'KR' => 'ko',
        'JP' => 'ja',
        'CN' => 'zh',
        'US' => 'en',
        'GB' => 'en',
        'ES' => 'es',
        'FR' => 'fr'
    ];
    
    return $countryLocales[$location->iso_code] ?? 'en';
}
```

### 브라우저 기반 감지
```php
public function detectBrowserLocale(Request $request)
{
    $acceptLanguage = $request->header('Accept-Language');
    
    if (!$acceptLanguage) {
        return config('app.locale');
    }
    
    // Parse Accept-Language header
    preg_match_all('/([a-z]{2})-?([A-Z]{2})?,?;?q?=?([0-9.]+)?/', 
        $acceptLanguage, $matches);
    
    $languages = [];
    foreach ($matches[1] as $i => $lang) {
        $priority = $matches[3][$i] ?: 1.0;
        $languages[$lang] = (float) $priority;
    }
    
    arsort($languages);
    
    foreach (array_keys($languages) as $lang) {
        if (in_array($lang, config('app.locales'))) {
            return $lang;
        }
    }
    
    return config('app.locale');
}
```

## 📊 번역 통계

### 번역 완성도 계산
```php
public function getTranslationStatistics($locale)
{
    $defaultTranslations = $this->getAllTranslations('en');
    $localeTranslations = $this->getAllTranslations($locale);
    
    $totalKeys = count($defaultTranslations);
    $translatedKeys = 0;
    $missingKeys = [];
    
    foreach ($defaultTranslations as $key => $value) {
        if (isset($localeTranslations[$key])) {
            $translatedKeys++;
        } else {
            $missingKeys[] = $key;
        }
    }
    
    return [
        'total' => $totalKeys,
        'translated' => $translatedKeys,
        'missing' => count($missingKeys),
        'percentage' => round(($translatedKeys / $totalKeys) * 100, 2),
        'missing_keys' => $missingKeys
    ];
}
```

## 📧 다국어 이메일

### 이메일 템플릿 구조
```
/jiny/auth/resources/views/emails/
├── ko/
│   ├── verification.blade.php
│   └── password-reset.blade.php
├── en/
│   ├── verification.blade.php
│   └── password-reset.blade.php
└── ja/
    ├── verification.blade.php
    └── password-reset.blade.php
```

### 언어별 이메일 발송
```php
public function sendVerificationEmail(User $user)
{
    $locale = $user->preferences->language ?? app()->getLocale();
    
    Mail::to($user)->locale($locale)->send(new VerificationEmail($user));
}
```

### Mailable 클래스
```php
class VerificationEmail extends Mailable
{
    public function build()
    {
        return $this->subject(__('emails.verification.subject'))
            ->view('emails.' . app()->getLocale() . '.verification');
    }
}
```

## 🚨 주의사항

1. **번역 키 관리**
   - 일관된 네이밍 컨벤션 사용
   - 중첩 구조 활용
   - 번역 누락 방지

2. **성능 최적화**
   - 번역 파일 캐싱
   - 자주 사용되는 번역 메모리 캐싱
   - 동적 로딩 최소화

3. **문자 인코딩**
   - UTF-8 인코딩 사용
   - RTL 언어 특별 처리
   - 특수 문자 이스케이프

4. **날짜/시간 처리**
   - Carbon 라이브러리 활용
   - 시간대 정확한 변환
   - 로컬 형식 적용

## 📝 활용 예시

### 시나리오 1: 사용자 언어 자동 설정
```php
// 회원가입 시 자동 언어 감지
public function register(Request $request)
{
    $user = User::create($request->validated());
    
    // 브라우저/IP 기반 언어 감지
    $detectedLocale = $this->detectUserLocale($request);
    
    // 사용자 설정에 저장
    $user->preferences()->create([
        'language' => $detectedLocale,
        'timezone' => $this->detectTimezone($request),
        'currency' => $this->detectCurrency($detectedLocale)
    ]);
    
    return redirect('/')->with('locale', $detectedLocale);
}
```

### 시나리오 2: 관리자 번역 편집
```php
// 인라인 번역 편집
public function updateTranslation(Request $request)
{
    $key = $request->key;     // 'auth.login.title'
    $locale = $request->locale; // 'ko'
    $value = $request->value;  // '로그인'
    
    // DB에 저장
    Translation::updateOrCreate(
        ['locale' => $locale, 'key' => $key],
        ['value' => $value, 'updated_by' => auth()->id()]
    );
    
    // 캐시 삭제
    Cache::forget("translations.{$locale}");
    
    return response()->json(['success' => true]);
}
```

### 시나리오 3: 다국어 검증 메시지
```php
// 커스텀 검증 메시지
$messages = [
    'email.required' => __('validation.custom.email.required'),
    'password.min' => __('validation.custom.password.min', ['min' => 8])
];

$validator = Validator::make($request->all(), [
    'email' => 'required|email',
    'password' => 'required|min:8'
], $messages);
```

## 🔧 도구 및 라이브러리

### 추천 패키지
1. **Laravel Lang** - 기본 번역 파일 모음
2. **Laravel Translation Manager** - 웹 기반 번역 관리
3. **Langman** - CLI 번역 관리 도구
4. **Carbon** - 다국어 날짜/시간 처리

### 번역 동기화
```bash
# 누락된 번역 키 찾기
php artisan translation:missing ko

# 번역 파일 동기화
php artisan translation:sync en ko

# 번역 내보내기/가져오기
php artisan translation:export ko --format=json
php artisan translation:import translations.json --locale=ko
```