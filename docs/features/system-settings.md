# 시스템 설정 (System Settings)

## 📋 개요
인증 시스템의 동작 방식을 관리자가 실시간으로 설정하고 조정할 수 있는 통합 설정 관리 시스템입니다.

## 🎯 주요 기능

### 1. 로그인 설정
- 세션 수명, 로그인 유지, 2FA 설정
- 로그인 시도 제한 및 계정 잠금 정책
- 다중 세션 및 디바이스 추적 설정

### 2. 회원가입 설정
- 가입 허용/차단, 이메일/휴대폰 인증
- 기본 등급/유형 및 가입 보상 설정
- 이메일 도메인 허용/차단 목록 관리

### 3. 보안 설정
- 비밀번호 정책 (길이, 복잡도, 만료)
- IP 화이트리스트 및 지역 차단
- 무차별 대입 공격 방어 설정

### 4. CAPTCHA 설정
- CAPTCHA 제공자 선택 (reCAPTCHA, hCaptcha)
- 페이지별 CAPTCHA 적용 설정
- 실패 횟수 기반 자동 활성화

## 🔗 라우트 (Routes)

| HTTP 메소드 | URI | 컨트롤러 메소드 | 설명 |
|------------|-----|----------------|------|
| GET | `/admin/auth/settings/login` | AdminAuthSettingsController::loginSettings | 로그인 설정 페이지 |
| POST | `/admin/auth/settings/login` | AdminAuthSettingsController::updateLoginSettings | 로그인 설정 업데이트 |
| GET | `/admin/auth/settings/registration` | AdminAuthSettingsController::registrationSettings | 가입 설정 페이지 |
| POST | `/admin/auth/settings/registration` | AdminAuthSettingsController::updateRegistrationSettings | 가입 설정 업데이트 |
| GET | `/admin/auth/settings/security` | AdminSecuritySettingsController::securitySettings | 보안 설정 페이지 |
| POST | `/admin/auth/settings/security` | AdminSecuritySettingsController::updateSecuritySettings | 보안 설정 업데이트 |
| GET | `/admin/auth/settings/captcha` | AdminSecuritySettingsController::captchaSettings | CAPTCHA 설정 페이지 |
| POST | `/admin/auth/settings/captcha` | AdminSecuritySettingsController::updateCaptchaSettings | CAPTCHA 설정 업데이트 |
| GET | `/admin/auth/settings/whitelist` | AdminSecuritySettingsController::ipWhitelist | IP 화이트리스트 관리 |
| POST | `/admin/auth/settings/whitelist` | AdminSecuritySettingsController::addIpWhitelist | IP 추가 |
| DELETE | `/admin/auth/settings/whitelist/{id}` | AdminSecuritySettingsController::removeIpWhitelist | IP 삭제 |

## 🎮 컨트롤러

### AdminAuthSettingsController
**위치**: `/jiny/auth/App/Http/Controllers/Admin/AdminAuthSettingsController.php`

#### updateLoginSettings(Request $request)
```php
// 요청 파라미터
'enable_remember_me' => ['required', 'in:true,false'],
'max_attempts' => ['required', 'integer', 'min:1', 'max:20'],
'lockout_duration' => ['required', 'integer', 'min:1', 'max:1440'],
'session_lifetime' => ['required', 'integer', 'min:10', 'max:10080'],
'enable_2fa' => ['required', 'in:true,false'],
'force_2fa_for_admin' => ['required', 'in:true,false'],
'allow_multiple_sessions' => ['required', 'in:true,false'],
'enable_device_tracking' => ['required', 'in:true,false']
```

#### updateRegistrationSettings(Request $request)
```php
// 요청 파라미터
'enable_registration' => ['required', 'in:true,false'],
'require_email_verification' => ['required', 'in:true,false'],
'require_phone_verification' => ['required', 'in:true,false'],
'require_terms_agreement' => ['required', 'in:true,false'],
'auto_approve' => ['required', 'in:true,false'],
'default_user_type' => ['required', 'string'],
'default_user_grade' => ['required', 'string'],
'welcome_point' => ['required', 'integer', 'min:0'],
'welcome_emoney' => ['required', 'integer', 'min:0'],
'allowed_domains' => ['nullable', 'string'],
'blocked_domains' => ['nullable', 'string']
```

### AdminSecuritySettingsController
**위치**: `/jiny/auth/App/Http/Controllers/Admin/AdminSecuritySettingsController.php`

#### updateSecuritySettings(Request $request)
```php
// 요청 파라미터
'password_min_length' => ['required', 'integer', 'min:6', 'max:32'],
'password_require_uppercase' => ['required', 'in:true,false'],
'password_require_lowercase' => ['required', 'in:true,false'],
'password_require_number' => ['required', 'in:true,false'],
'password_require_special' => ['required', 'in:true,false'],
'password_expiry_days' => ['required', 'integer', 'min:0', 'max:365'],
'password_history_count' => ['required', 'integer', 'min:0', 'max:10'],
'enable_ip_whitelist' => ['required', 'in:true,false'],
'enable_geo_blocking' => ['required', 'in:true,false'],
'blocked_countries' => ['nullable', 'array']
```

## 💾 데이터베이스 테이블

### auth_settings
모든 설정을 저장하는 key-value 저장소

| 컬럼명 | 타입 | 설명 |
|--------|------|------|
| id | BIGINT | Primary Key |
| group | VARCHAR | 설정 그룹 (login, registration, security, captcha) |
| key | VARCHAR | 설정 키 |
| value | TEXT | 설정 값 (JSON 가능) |
| type | VARCHAR | 값 타입 (text, boolean, integer, json) |
| description | TEXT | 설정 설명 |
| is_encrypted | BOOLEAN | 암호화 여부 |

### auth_ip_whitelist
IP 화이트리스트

| 컬럼명 | 타입 | 설명 |
|--------|------|------|
| id | BIGINT | Primary Key |
| ip_address | VARCHAR(45) | IP 주소 (IPv4/IPv6) |
| description | VARCHAR | 설명 |
| added_by | BIGINT | 추가한 관리자 ID |
| is_active | BOOLEAN | 활성화 여부 |

## 🛠 서비스 클래스

### AuthSettingsService
**위치**: `/jiny/auth/App/Services/AuthSettingsService.php`

주요 메소드:
```php
// 설정 값 가져오기
AuthSettingsService::get('login', 'max_attempts', 5);

// 설정 값 저장
AuthSettingsService::set('login', 'max_attempts', 10, 'integer');

// 그룹별 모든 설정 가져오기
AuthSettingsService::getGroup('login');

// 그룹별 설정 업데이트
AuthSettingsService::updateGroup('login', $settings);

// 특화 메소드
AuthSettingsService::getLoginSettings();
AuthSettingsService::getRegistrationSettings();
AuthSettingsService::getSecuritySettings();
AuthSettingsService::getCaptchaSettings();
```

## 🛡 미들웨어

### ApplyAuthSettings
**위치**: `/jiny/auth/App/Http/Middleware/ApplyAuthSettings.php`

설정을 실시간으로 적용하는 미들웨어:
- 로그인 시도 제한 적용
- 회원가입 차단/허용
- IP 화이트리스트 검증
- 의심스러운 로그인 감지

## 📦 설정 예시

### 로그인 설정
```json
{
    "enable_remember_me": true,
    "max_attempts": 5,
    "lockout_duration": 15,
    "session_lifetime": 120,
    "enable_2fa": false,
    "force_2fa_for_admin": true,
    "allow_multiple_sessions": true,
    "enable_device_tracking": true
}
```

### 회원가입 설정
```json
{
    "enable_registration": true,
    "require_email_verification": true,
    "require_phone_verification": false,
    "require_terms_agreement": true,
    "auto_approve": true,
    "default_user_type": "general",
    "default_user_grade": "bronze",
    "welcome_point": 1000,
    "welcome_emoney": 0,
    "allowed_domains": [],
    "blocked_domains": ["tempmail.com", "guerrillamail.com"]
}
```

### 보안 설정
```json
{
    "password_min_length": 8,
    "password_require_uppercase": true,
    "password_require_lowercase": true,
    "password_require_number": true,
    "password_require_special": false,
    "password_expiry_days": 90,
    "password_history_count": 3,
    "enable_ip_whitelist": false,
    "enable_geo_blocking": false,
    "blocked_countries": [],
    "enable_brute_force_protection": true,
    "enable_suspicious_login_detection": true
}
```

## 🎨 뷰 파일

### 로그인 설정 페이지
**위치**: `/jiny/auth/resources/views/admin/settings/login.blade.php`

주요 UI 요소:
- 세션 설정 섹션
- 로그인 시도 제한 설정
- 2FA 설정
- 디바이스 추적 설정

### 회원가입 설정 페이지
**위치**: `/jiny/auth/resources/views/admin/settings/registration.blade.php`

주요 UI 요소:
- 가입 허용/차단 토글
- 인증 요구사항 체크박스
- 기본값 설정 (등급, 유형, 보상)
- 도메인 관리 텍스트 영역

### 보안 설정 페이지
**위치**: `/jiny/auth/resources/views/admin/settings/security.blade.php`

주요 UI 요소:
- 비밀번호 정책 슬라이더
- 국가 차단 멀티셀렉트
- 보안 기능 토글 스위치

## ⚙️ 캐싱

설정값은 성능 최적화를 위해 캐시됩니다:
- 캐시 키: `auth_settings.{group}.{key}`
- 캐시 시간: 3600초 (1시간)
- 설정 변경 시 자동 캐시 삭제

## 📝 활용 예시

### 시나리오 1: 보안 강화 설정
```javascript
// 비밀번호 정책 강화
fetch('/admin/auth/settings/security', {
    method: 'POST',
    body: JSON.stringify({
        password_min_length: 12,
        password_require_uppercase: true,
        password_require_lowercase: true,
        password_require_number: true,
        password_require_special: true,
        password_expiry_days: 60,
        password_history_count: 5,
        enable_brute_force_protection: true
    })
});

// 2FA 강제 적용
fetch('/admin/auth/settings/login', {
    method: 'POST',
    body: JSON.stringify({
        enable_2fa: true,
        force_2fa_for_admin: true
    })
});
```

### 시나리오 2: 특정 기업용 설정
```javascript
// 특정 도메인만 가입 허용
fetch('/admin/auth/settings/registration', {
    method: 'POST',
    body: JSON.stringify({
        enable_registration: true,
        allowed_domains: 'company.com, partner.com',
        require_email_verification: true,
        auto_approve: false // 수동 승인
    })
});

// 사내 IP만 관리자 접근 허용
fetch('/admin/auth/settings/whitelist', {
    method: 'POST',
    body: JSON.stringify({
        ip_address: '192.168.1.0/24',
        description: '사내 네트워크'
    })
});
```

## 🚨 주의사항

1. **설정 변경 영향**
   - 실시간으로 모든 사용자에게 적용
   - 잘못된 설정 시 로그인 불가능 상황 발생 가능

2. **암호화된 설정**
   - API 키, 비밀 키는 자동 암호화
   - 복호화는 시스템 내부에서만 가능

3. **기본값 복원**
   - 설정 오류 시 DB에서 직접 수정 필요
   - 백업 설정 유지 권장

4. **캐시 동기화**
   - 다중 서버 환경에서 캐시 동기화 필요
   - Redis 등 중앙 캐시 사용 권장

## 🔄 자동화 및 스크립팅

### 설정 백업
```bash
# 모든 설정을 JSON으로 내보내기
php artisan auth:settings:export > settings_backup.json
```

### 설정 복원
```bash
# JSON에서 설정 가져오기
php artisan auth:settings:import settings_backup.json
```

### 설정 초기화
```bash
# 모든 설정을 기본값으로 초기화
php artisan auth:settings:reset
```

## 🔌 API 엔드포인트

설정 조회 API (읽기 전용):
```
GET /api/admin/auth/settings/{group}
Authorization: Bearer {admin_token}
```

응답:
```json
{
    "group": "login",
    "settings": {
        "max_attempts": 5,
        "lockout_duration": 15,
        ...
    }
}
```