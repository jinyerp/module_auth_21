# 1단계: 기본 인증 기능 (Basic Authentication)

## 📋 개요
Laravel 기반의 기본적인 사용자 인증 시스템으로 로그인, 로그아웃, 회원가입, 사용자 관리 기능을 제공합니다.

## 🎯 주요 기능

### 1.1 세션 기반 로그인/로그아웃
전통적인 쿠키-세션 방식의 인증 시스템

### 1.2 JWT 토큰 인증
API 기반 애플리케이션을 위한 토큰 인증

### 1.3 회원가입
이메일/비밀번호 기반 회원가입 시스템

### 1.4 사용자 관리
관리자 페이지에서 사용자 CRUD 관리

## 🔗 라우트 (Routes)

### 세션 인증 라우트
| HTTP 메소드 | URI | 컨트롤러 메소드 | 설명 |
|------------|-----|----------------|------|
| GET | `/login` | AuthController::showLogin | 로그인 페이지 |
| POST | `/login` | AuthController::login | 로그인 처리 |
| POST | `/logout` | AuthController::logout | 로그아웃 처리 |
| GET | `/register` | AuthController::showRegister | 회원가입 페이지 |
| POST | `/register` | AuthController::register | 회원가입 처리 |
| GET | `/home` | AuthController::home | 사용자 홈 대시보드 |

### JWT 인증 라우트
| HTTP 메소드 | URI | 컨트롤러 메소드 | 설명 |
|------------|-----|----------------|------|
| GET | `/signin` | AuthJWTController::signinForm | JWT 로그인 페이지 |
| POST | `/signin` | AuthJWTController::signin | JWT 로그인 처리 |
| GET | `/signup` | AuthJWTController::signupForm | JWT 회원가입 페이지 |
| POST | `/signup` | AuthJWTController::signup | JWT 회원가입 처리 |
| POST | `/signout` | AuthJWTController::signout | JWT 로그아웃 |
| POST | `/refresh` | AuthJWTController::refresh | 토큰 갱신 |
| GET | `/profile` | AuthJWTController::profile | 프로필 조회 |

### 관리자 사용자 관리
| HTTP 메소드 | URI | 컨트롤러 메소드 | 설명 |
|------------|-----|----------------|------|
| GET | `/admin/auth/users` | AdminUsers::index | 사용자 목록 |
| GET | `/admin/auth/users/create` | AdminUsers::create | 사용자 생성 폼 |
| POST | `/admin/auth/users` | AdminUsersCreate::store | 사용자 생성 |
| GET | `/admin/auth/users/{id}/edit` | AdminUsersEdit::edit | 사용자 수정 폼 |
| PUT | `/admin/auth/users/{id}` | AdminUsersEdit::update | 사용자 수정 |
| DELETE | `/admin/auth/users/{id}` | AdminUsersDelete::destroy | 사용자 삭제 |

## 🎮 컨트롤러

### AuthController (세션 인증)
**위치**: `/jiny/auth/App/Http/Controllers/AuthController.php`

#### login(Request $request)
```php
// 요청 파라미터
'email' => ['required', 'email'],
'password' => ['required', 'string'],
'remember' => ['nullable', 'boolean']

// 응답
성공: redirect()->intended('/home')
실패: redirect()->back()->withErrors()
```

#### register(Request $request)
```php
// 요청 파라미터
'name' => ['required', 'string', 'max:255'],
'email' => ['required', 'email', 'unique:users'],
'password' => ['required', 'min:8', 'confirmed'],
'terms' => ['required', 'accepted']

// 응답
성공: redirect('/home')
실패: redirect()->back()->withErrors()
```

### AuthJWTController (JWT 인증)
**위치**: `/jiny/auth/App/Http/Controllers/AuthJWTController.php`

#### signin(Request $request)
```php
// 요청 파라미터
'email' => ['required', 'email'],
'password' => ['required', 'string']

// 응답 (JSON)
{
    "success": true,
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "token_type": "bearer",
    "expires_in": 3600,
    "user": {
        "id": 1,
        "name": "홍길동",
        "email": "user@example.com"
    }
}
```

## 💾 데이터베이스 테이블

### users (사용자 테이블)
| 컬럼명 | 타입 | 설명 |
|--------|------|------|
| id | BIGINT | Primary Key |
| name | VARCHAR | 사용자 이름 |
| email | VARCHAR | 이메일 (Unique) |
| password | VARCHAR | 암호화된 비밀번호 |
| email_verified_at | TIMESTAMP | 이메일 인증 시간 |
| remember_token | VARCHAR | 자동 로그인 토큰 |
| status | ENUM | active/suspended/dormant |
| is_admin | BOOLEAN | 관리자 여부 |
| created_at | TIMESTAMP | 가입일 |
| updated_at | TIMESTAMP | 수정일 |

### auth_login_histories (로그인 이력)
| 컬럼명 | 타입 | 설명 |
|--------|------|------|
| id | BIGINT | Primary Key |
| user_id | BIGINT | 사용자 ID |
| ip_address | VARCHAR | 로그인 IP |
| user_agent | TEXT | 브라우저 정보 |
| status | ENUM | success/failed |
| created_at | TIMESTAMP | 로그인 시도 시간 |

## 🛡 미들웨어

### auth
인증된 사용자만 접근 가능
```php
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
});
```

### guest
비인증 사용자만 접근 가능
```php
Route::middleware(['guest'])->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin']);
});
```

### jwt.auth
JWT 토큰 검증
```php
Route::middleware(['jwt.auth'])->group(function () {
    Route::get('/api/user', [ApiController::class, 'user']);
});
```

## 🎨 뷰 파일

### 로그인 페이지
**위치**: `/jiny/auth/resources/views/login.blade.php`

주요 요소:
- 이메일 입력 필드
- 비밀번호 입력 필드
- 자동 로그인 체크박스
- 비밀번호 찾기 링크
- 회원가입 링크

### 회원가입 페이지
**위치**: `/jiny/auth/resources/views/register.blade.php`

주요 요소:
- 이름, 이메일, 비밀번호 필드
- 비밀번호 확인 필드
- 약관 동의 체크박스
- 가입 버튼

### 사용자 홈
**위치**: `/jiny/auth/resources/views/home.blade.php`

주요 요소:
- 환영 메시지
- 사용자 정보 요약
- 최근 활동 내역
- 빠른 메뉴

## ⚙️ 설정

### 세션 설정
```php
// config/session.php
'driver' => env('SESSION_DRIVER', 'file'),
'lifetime' => env('SESSION_LIFETIME', 120),
'expire_on_close' => false,
'encrypt' => false,
'cookie' => env('SESSION_COOKIE', 'laravel_session'),
```

### JWT 설정
```php
// config/jwt.php
'ttl' => env('JWT_TTL', 60), // 분 단위
'refresh_ttl' => env('JWT_REFRESH_TTL', 20160), // 2주
'algo' => env('JWT_ALGO', 'HS256'),
'secret' => env('JWT_SECRET'),
```

## 📦 Request/Response 예시

### 세션 로그인
```http
POST /login
Content-Type: application/x-www-form-urlencoded

email=user@example.com&password=password123&remember=1
```

### JWT 로그인
```http
POST /signin
Content-Type: application/json

{
    "email": "user@example.com",
    "password": "password123"
}
```

응답:
```json
{
    "success": true,
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "token_type": "bearer",
    "expires_in": 3600
}
```

### JWT API 호출
```http
GET /api/profile
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
```

## 🔒 보안 고려사항

1. **비밀번호 암호화**
   - bcrypt 해싱 사용 (기본 라운드: 10)
   - 평문 비밀번호는 절대 저장하지 않음

2. **CSRF 보호**
   - 모든 POST 요청에 CSRF 토큰 필수
   - Laravel의 VerifyCsrfToken 미들웨어 사용

3. **세션 보안**
   - HTTPS 환경에서만 쿠키 전송 (secure flag)
   - httpOnly 플래그로 XSS 방지

4. **로그인 시도 제한**
   - 5회 실패 시 15분간 계정 잠금
   - IP 기반 rate limiting

5. **JWT 보안**
   - 짧은 만료 시간 설정 (1시간)
   - Refresh 토큰으로 재발급
   - 블랙리스트 관리

## 📝 활용 예시

### 시나리오 1: 일반 웹 애플리케이션
```php
// 로그인 후 리다이렉션
public function login(Request $request)
{
    $credentials = $request->only('email', 'password');
    
    if (Auth::attempt($credentials, $request->remember)) {
        // 로그인 성공
        return redirect()->intended('dashboard');
    }
    
    // 로그인 실패
    return back()->withErrors([
        'email' => '인증 정보가 일치하지 않습니다.',
    ]);
}
```

### 시나리오 2: SPA 애플리케이션
```javascript
// Vue.js에서 JWT 로그인
async function login(email, password) {
    const response = await axios.post('/signin', {
        email: email,
        password: password
    });
    
    if (response.data.success) {
        // 토큰 저장
        localStorage.setItem('token', response.data.token);
        
        // Axios 기본 헤더 설정
        axios.defaults.headers.common['Authorization'] = 
            `Bearer ${response.data.token}`;
    }
}
```

### 시나리오 3: 모바일 앱 인증
```javascript
// React Native에서 토큰 관리
import AsyncStorage from '@react-native-async-storage/async-storage';

async function authenticate(email, password) {
    const response = await fetch('https://api.example.com/signin', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({email, password})
    });
    
    const data = await response.json();
    
    if (data.success) {
        // 토큰 저장
        await AsyncStorage.setItem('auth_token', data.token);
        await AsyncStorage.setItem('refresh_token', data.refresh_token);
    }
}
```

## 🚨 주의사항

1. **세션과 JWT 혼용 금지**
   - 한 애플리케이션에서는 하나의 인증 방식만 사용
   - API는 JWT, 웹은 세션으로 분리

2. **토큰 저장 위치**
   - localStorage: XSS 취약
   - httpOnly Cookie: 권장
   - 모바일: Secure Storage 사용

3. **자동 로그인**
   - 민감한 작업 시 재인증 요구
   - 장기간 미사용 시 자동 로그아웃

4. **관리자 계정**
   - 별도의 강화된 인증 프로세스
   - 접근 IP 제한 권장