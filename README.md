# Jiny Auth 패키지

## 개요

Jiny Auth는 Laravel 12 애플리케이션을 위한 포괄적인 인증 및 사용자 관리 패키지입니다. 현대적인 기능과 보안 모범 사례를 갖춘 완전한 인증 시스템을 제공합니다.

## 주요 기능

### 인증 시스템
- 세션 관리를 통한 사용자 로그인/로그아웃
- 이메일 인증을 통한 사용자 등록
- 비밀번호 재설정 및 복구
- 자동 로그인 기능
- 소셜 인증 지원 (OAuth)
- 다단계 인증 (2FA)

### 사용자 관리
- 완전한 사용자 CRUD 작업
- 사용자 역할 및 권한 관리
- 사용자 프로필 관리
- 계정 활성화 및 인증
- 사용자 상태 관리 (활성, 정지 등)

### 보안 기능
- 안전한 비밀번호 해싱 (bcrypt/argon2)
- CSRF 보호
- 로그인 제한 및 속도 제한
- 세션 보안 및 관리
- 비밀번호 만료 정책
- 로그인 시도 추적
- IP 기반 보안 규칙

### 추가 기능
- 동적 UI를 위한 Livewire 3 컴포넌트
- 사용자 정의 가능한 인증 뷰
- 이메일 알림
- 사용자 관리를 위한 Artisan 명령어
- 포괄적인 로깅
- 다국어 지원
- E-Money 시스템
- 사용자 샤딩 지원
- 휴면 계정 관리

## 설치 방법

### 요구사항
- PHP >= 8.2
- Laravel 11.x 또는 12.x
- Livewire 3.x

### Composer를 통한 설치

```bash
composer require jiny/auth
```

### 설정 파일 퍼블리싱

```bash
# 설정 파일 퍼블리싱
php artisan vendor:publish --tag=jiny-auth-config

# 뷰 파일 퍼블리싱 (선택사항)
php artisan vendor:publish --tag=jiny-auth-views

# 마이그레이션 파일 퍼블리싱 (선택사항)
php artisan vendor:publish --tag=jiny-auth-migrations
```

### 마이그레이션 실행

```bash
php artisan migrate
```

## 설정

설정 파일은 `config/jiny-auth.php`에 퍼블리싱됩니다. 다양한 인증 설정을 사용자 정의할 수 있습니다:

```php
return [
    // 기능 활성화/비활성화
    'features' => [
        'registration' => true,
        'password_reset' => true,
        'email_verification' => true,
        'two_factor_auth' => false,
        'social_login' => false,
        'remember_me' => true,
        'account_deletion' => true,
    ],

    // 비밀번호 정책
    'password' => [
        'min_length' => 8,
        'max_length' => 255,
        'require_uppercase' => true,
        'require_lowercase' => true,
        'require_numeric' => true,
        'require_special_char' => true,
        'expiration_days' => 90,
        'history_count' => 5,
    ],

    // 로그인 설정
    'login' => [
        'max_attempts' => 5,
        'lockout_duration' => 60, // 분
        'remember_me_duration' => 30, // 일
        'username_field' => 'email',
        'case_sensitive' => false,
        'track_login_attempts' => true,
        'track_login_history' => true,
    ],

    // 세션 설정
    'session' => [
        'lifetime' => 120, // 분
        'expire_on_close' => false,
        'encrypt' => false,
        'single_session' => false,
        'track_ip' => true,
        'track_user_agent' => true,
    ],
];
```

## 폴더 구조

```
jiny/auth/
├── App/
│   ├── Console/Commands/          # Artisan 명령어
│   │   ├── CreateUserShards.php
│   │   └── UserDormantCheckCommand.php
│   ├── Http/
│   │   ├── Controllers/           # 컨트롤러
│   │   │   ├── Admin/            # 관리자 컨트롤러
│   │   │   ├── Api/              # API 컨트롤러
│   │   │   ├── Auth/             # 인증 컨트롤러
│   │   │   ├── Emoney/           # E-Money 컨트롤러
│   │   │   ├── Home/             # 홈 컨트롤러
│   │   │   ├── Jwt/              # JWT 컨트롤러
│   │   │   ├── Profile/          # 프로필 컨트롤러
│   │   │   ├── Social/           # 소셜 로그인 컨트롤러
│   │   │   └── Users/            # 사용자 컨트롤러
│   │   ├── Livewire/             # Livewire 컴포넌트
│   │   │   ├── Emoney/
│   │   │   ├── Profile/
│   │   │   ├── Social/
│   │   │   └── Users/
│   │   └── Middleware/           # 미들웨어
│   ├── Models/                   # Eloquent 모델
│   │   ├── Account.php
│   │   ├── AccountLog.php
│   │   ├── Blacklist.php
│   │   ├── Country.php
│   │   ├── DormantAccount.php
│   │   ├── Grade.php
│   │   ├── LoginHistory.php
│   │   ├── PasswordError.php
│   │   ├── Role.php
│   │   ├── TermLog.php
│   │   ├── Terms.php
│   │   ├── TwoFactorAuth.php
│   │   └── UserShardingConfig.php
│   └── Services/                 # 서비스 클래스
│       ├── Dormant/
│       ├── Sharding/
│       └── Terms/
├── config/                       # 설정 파일
│   ├── auth.php
│   └── oauth_provider.php
├── database/                     # 데이터베이스
│   ├── migrations/              # 마이그레이션 파일
│   └── seeders/                 # 시더 파일
├── resources/                    # 리소스
│   └── views/                   # 뷰 파일
│       ├── admin/
│       ├── auth/
│       ├── emails/
│       ├── emoney/
│       ├── home/
│       ├── jwt/
│       ├── layouts/
│       ├── livewire/
│       ├── partials/
│       ├── profile/
│       ├── template/
│       └── users/
├── routes/                       # 라우트 파일
│   ├── admin.php
│   ├── api.php
│   ├── auth.php
│   └── web.php
├── tests/                        # 테스트 파일
│   ├── Feature/
│   └── Unit/
├── JinyAuthServiceProvider.php   # 서비스 프로바이더
└── README.md
```

## 사용법

### 기본 인증 라우트

패키지는 다음 라우트를 자동으로 등록합니다:

#### 일반 사용자 라우트
- `/login` - 로그인 페이지
- `/register` - 회원가입 페이지
- `/forgot-password` - 비밀번호 재설정 요청
- `/reset-password/{token}` - 비밀번호 재설정
- `/email/verify` - 이메일 인증
- `/logout` - 로그아웃

#### JWT 인증 라우트
- `/signin` - JWT 로그인
- `/signup` - JWT 회원가입
- `/signout` - JWT 로그아웃

#### 소셜 로그인 라우트
- `/auth/social/{provider}` - 소셜 로그인 리디렉션
- `/auth/social/{provider}/callback` - 소셜 로그인 콜백

#### 프로필 관리 라우트
- `/profile/avatar` - 아바타 관리
- `/profile/addresses` - 주소 관리
- `/profile/security` - 보안 설정
- `/profile/social` - 소셜 계정 연결

#### E-Money 라우트
- `/emoney` - 잔액 조회
- `/emoney/deposit` - 입금
- `/emoney/withdraw` - 출금
- `/emoney/bank` - 은행 계좌 관리

#### 관리자 라우트 (jiny/admin 설치 시)
- `/admin/terms` - 약관 관리
- `/admin/password-errors` - 비밀번호 오류 관리
- `/admin/sharding` - 샤딩 관리

### 라우트 보호

제공된 미들웨어를 사용하여 라우트를 보호하세요:

```php
Route::middleware(['auth', 'auth.verified'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    });
});

// 2FA 보호
Route::middleware(['auth', 'auth.2fa'])->group(function () {
    Route::get('/admin', function () {
        return view('admin.dashboard');
    });
});
```

### Artisan 명령어

#### 사용자 관리
```bash
# 사용자 샤드 테이블 생성
php artisan user:shards

# 사용자 샤드 테이블 삭제
php artisan user:shards --rollback

# 휴면 계정 전환
php artisan user:dormant-check

# 휴면 계정 전환 (테스트 모드)
php artisan user:dormant-check --dry-run

# 휴면 계정 전환 (사용자 정의 일수)
php artisan user:dormant-check --days=180
```

### Livewire 컴포넌트 사용

```blade
{{-- 프로필 상태 --}}
<livewire:auth.profile.status />

{{-- 프로필 계정 --}}
<livewire:auth.profile.account />

{{-- E-Money 관리 --}}
<livewire:auth.emoney.my.balance />
<livewire:auth.emoney.my.deposit />
<livewire:auth.emoney.my.withdraw />

{{-- 사용자 메시지 --}}
<livewire:auth.users.message />

{{-- 사용자 리뷰 --}}
<livewire:auth.users.reviews />
```

### 뷰 커스터마이징

뷰를 퍼블리싱한 후 다음 위치에서 커스터마이징할 수 있습니다:
`resources/views/vendor/jiny-auth/`

### API 사용법

API 인증을 위해 제공된 서비스를 사용하세요:

```php
use Jiny\Auth\App\Http\Controllers\Api\ApiAuthController;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        
        if (Auth::attempt($credentials)) {
            // 인증 성공
            return response()->json(['message' => '로그인 성공']);
        }

        return response()->json(['message' => '잘못된 인증 정보'], 401);
    }
}
```

## 이벤트

패키지는 다양한 이벤트를 발생시킵니다:

- `UserRegistered` - 새 사용자가 등록될 때
- `UserLoggedIn` - 사용자가 로그인할 때
- `UserLoggedOut` - 사용자가 로그아웃할 때
- `PasswordReset` - 비밀번호가 재설정될 때
- `EmailVerified` - 이메일이 인증될 때
- `LoginAttemptFailed` - 로그인 시도가 실패할 때

## 테스트

```bash
# 패키지 테스트 실행
vendor/bin/phpunit

# 특정 테스트 스위트 실행
vendor/bin/phpunit --testsuite=Feature
vendor/bin/phpunit --testsuite=Unit
```

## 보안

보안 관련 문제를 발견하시면 이슈 트래커 대신 infohojin@gmail.com으로 이메일을 보내주세요.

## 라이선스

MIT 라이선스 (MIT). 자세한 내용은 [라이선스 파일](LICENSE.md)을 참조하세요.

## 크레딧

- [JinyPHP 팀](https://github.com/jinyphp)
- [모든 기여자들](../../contributors)

## 지원

지원이 필요하시면 infohojin@gmail.com으로 이메일을 보내거나 [GitHub Issues](https://github.com/jinyphp/auth/issues)를 방문해주세요.