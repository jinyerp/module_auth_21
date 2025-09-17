# 2단계: 이메일 인증 및 비밀번호 관리

## 📋 개요
이메일 인증, 비밀번호 재설정, 계정 상태 관리, 승인 시스템 등 계정 보안과 관련된 고급 기능을 제공합니다.

## 🎯 주요 기능

### 2.1 이메일 인증
- 회원가입 시 이메일 인증 링크 발송
- 인증 토큰 관리 및 만료 처리
- 재발송 기능

### 2.2 비밀번호 관리
- 비밀번호 재설정 (이메일 링크)
- 비밀번호 변경
- 비밀번호 정책 적용

### 2.3 계정 상태 관리
- 계정 활성화/비활성화
- 계정 잠금/해제
- 계정 삭제 (소프트 삭제)

### 2.4 회원가입 승인
- 관리자 승인 대기
- 자동/수동 승인 설정
- 대량 승인 처리

### 2.5 API 인증 (Sanctum)
- API 토큰 발급 및 관리
- 토큰 권한 설정
- 토큰 취소

## 🔗 라우트 (Routes)

### 이메일 인증
| HTTP 메소드 | URI | 컨트롤러 메소드 | 설명 |
|------------|-----|----------------|------|
| GET | `/email/verify` | EmailVerificationController::notice | 인증 안내 페이지 |
| GET | `/email/verify/{id}/{hash}` | EmailVerificationController::verify | 이메일 인증 처리 |
| POST | `/email/resend` | EmailVerificationController::resend | 인증 메일 재발송 |

### 비밀번호 재설정
| HTTP 메소드 | URI | 컨트롤러 메소드 | 설명 |
|------------|-----|----------------|------|
| GET | `/forgot-password` | PasswordResetController::showLinkRequestForm | 비밀번호 찾기 폼 |
| POST | `/forgot-password` | PasswordResetController::sendResetLinkEmail | 재설정 링크 발송 |
| GET | `/reset-password/{token}` | PasswordResetController::showResetForm | 재설정 폼 |
| POST | `/reset-password` | PasswordResetController::reset | 비밀번호 재설정 |

### 비밀번호 변경
| HTTP 메소드 | URI | 컨트롤러 메소드 | 설명 |
|------------|-----|----------------|------|
| GET | `/password/change` | PasswordChangeController::showChangeForm | 비밀번호 변경 폼 |
| POST | `/password/change` | PasswordChangeController::change | 비밀번호 변경 처리 |

### 계정 상태 관리
| HTTP 메소드 | URI | 컨트롤러 메소드 | 설명 |
|------------|-----|----------------|------|
| POST | `/account/activate` | AccountStatusController::activate | 계정 활성화 |
| POST | `/account/deactivate` | AccountStatusController::deactivate | 계정 비활성화 |
| DELETE | `/account` | AccountStatusController::delete | 계정 삭제 |

### 회원가입 승인 (관리자)
| HTTP 메소드 | URI | 컨트롤러 메소드 | 설명 |
|------------|-----|----------------|------|
| GET | `/admin/auth/approval` | AdminApprovalController::index | 승인 대기 목록 |
| GET | `/admin/auth/approval/{id}` | AdminApprovalController::show | 상세 정보 |
| POST | `/admin/auth/approval/{id}/approve` | AdminApprovalController::approve | 승인 |
| POST | `/admin/auth/approval/{id}/reject` | AdminApprovalController::reject | 거부 |
| POST | `/admin/auth/approval/bulk-approve` | AdminApprovalController::bulkApprove | 일괄 승인 |

### API 인증 (Sanctum)
| HTTP 메소드 | URI | 컨트롤러 메소드 | 설명 |
|------------|-----|----------------|------|
| POST | `/api/register` | ApiAuthController::register | API 회원가입 |
| POST | `/api/login` | ApiAuthController::login | API 로그인 |
| POST | `/api/logout` | ApiAuthController::logout | API 로그아웃 |
| GET | `/api/user` | ApiAuthController::user | 현재 사용자 정보 |
| POST | `/api/tokens/create` | ApiTokenController::create | 토큰 생성 |
| GET | `/api/tokens` | ApiTokenController::index | 토큰 목록 |
| DELETE | `/api/tokens/{id}` | ApiTokenController::revoke | 토큰 취소 |

## 🎮 컨트롤러

### EmailVerificationController
**위치**: `/jiny/auth/App/Http/Controllers/EmailVerificationController.php`

#### verify(Request $request, $id, $hash)
```php
// URL 파라미터
$id: 사용자 ID
$hash: 인증 해시

// 처리 로직
1. 사용자 확인
2. 해시 검증
3. email_verified_at 업데이트
4. 인증 완료 리다이렉트
```

### PasswordResetController
**위치**: `/jiny/auth/App/Http/Controllers/PasswordResetController.php`

#### sendResetLinkEmail(Request $request)
```php
// 요청 파라미터
'email' => ['required', 'email', 'exists:users']

// 처리 로직
1. 토큰 생성
2. password_resets 테이블에 저장
3. 이메일 발송
```

### AdminApprovalController
**위치**: `/jiny/auth/App/Http/Controllers/Admin/AdminApprovalController.php`

#### approve(Request $request, $id)
```php
// 처리 로직
1. 사용자 상태를 'approved'로 변경
2. email_verified_at 설정
3. 환영 이메일 발송
4. 활동 로그 기록
```

## 💾 데이터베이스 테이블

### password_resets
| 컬럼명 | 타입 | 설명 |
|--------|------|------|
| email | VARCHAR | 사용자 이메일 |
| token | VARCHAR | 재설정 토큰 |
| created_at | TIMESTAMP | 생성 시간 |

### password_reset_tokens
| 컬럼명 | 타입 | 설명 |
|--------|------|------|
| email | VARCHAR | Primary Key |
| token | VARCHAR | 해시된 토큰 |
| created_at | TIMESTAMP | 생성 시간 |

### email_verifications
| 컬럼명 | 타입 | 설명 |
|--------|------|------|
| id | BIGINT | Primary Key |
| user_id | BIGINT | 사용자 ID |
| token | VARCHAR | 인증 토큰 |
| expires_at | TIMESTAMP | 만료 시간 |
| verified_at | TIMESTAMP | 인증 시간 |

### personal_access_tokens (Sanctum)
| 컬럼명 | 타입 | 설명 |
|--------|------|------|
| id | BIGINT | Primary Key |
| tokenable_type | VARCHAR | 모델 타입 |
| tokenable_id | BIGINT | 모델 ID |
| name | VARCHAR | 토큰 이름 |
| token | VARCHAR | 해시된 토큰 |
| abilities | TEXT | 권한 목록 |
| last_used_at | TIMESTAMP | 마지막 사용 시간 |

## 🛡 미들웨어

### verified
이메일 인증된 사용자만 접근
```php
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
});
```

### password.confirm
비밀번호 재확인이 필요한 민감한 작업
```php
Route::middleware(['password.confirm'])->group(function () {
    Route::post('/settings/security', [SecurityController::class, 'update']);
});
```

## 📧 이메일 템플릿

### 이메일 인증 메일
**위치**: `/jiny/auth/resources/views/emails/verify.blade.php`

```blade
@component('mail::message')
# 이메일 인증

아래 버튼을 클릭하여 이메일을 인증해주세요.

@component('mail::button', ['url' => $verificationUrl])
이메일 인증하기
@endcomponent

이 링크는 {{ $expiration }} 시간 후 만료됩니다.

감사합니다,<br>
{{ config('app.name') }}
@endcomponent
```

### 비밀번호 재설정 메일
**위치**: `/jiny/auth/resources/views/emails/reset-password.blade.php`

## ⚙️ 설정

### 이메일 인증 설정
```php
// config/auth.php
'verification' => [
    'expire' => 60, // 분 단위
    'throttle' => 60, // 재발송 제한 (초)
]
```

### 비밀번호 정책
```php
// config/auth.php
'password_rules' => [
    'min' => 8,
    'require_uppercase' => true,
    'require_number' => true,
    'require_special' => false,
]
```

### Sanctum 설정
```php
// config/sanctum.php
'expiration' => null, // 토큰 만료 시간 (분)
'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),
```

## 📦 Request/Response 예시

### 이메일 인증 재발송
```http
POST /email/resend
X-CSRF-TOKEN: {{ csrf_token }}
```

### 비밀번호 재설정 요청
```http
POST /forgot-password
Content-Type: application/json

{
    "email": "user@example.com"
}
```

### API 토큰 생성
```http
POST /api/tokens/create
Authorization: Bearer {user-token}
Content-Type: application/json

{
    "name": "Mobile App",
    "abilities": ["read", "write"]
}
```

응답:
```json
{
    "token": "1|kZzmKl6RXPMf6Tz3Gw7rVNlOB8fQ9xEA5iHJKL2D",
    "plain_text_token": "1|kZzmKl6RXPMf6Tz3Gw7rVNlOB8fQ9xEA5iHJKL2D"
}
```

## 🔒 보안 고려사항

1. **토큰 보안**
   - 일회용 토큰 사용
   - 만료 시간 설정 (기본 60분)
   - 사용 후 즉시 삭제

2. **Rate Limiting**
   - 이메일 발송: 분당 1회
   - 비밀번호 재설정: 시간당 5회
   - API 호출: 분당 60회

3. **비밀번호 정책**
   - 최소 8자 이상
   - 대소문자 조합
   - 숫자 포함
   - 이전 비밀번호 재사용 금지

4. **승인 시스템**
   - 민감한 도메인 수동 승인
   - 대량 가입 감지 및 차단

## 📝 활용 예시

### 시나리오 1: 이메일 인증 플로우
```php
// 회원가입 후 이메일 발송
public function register(Request $request)
{
    $user = User::create($request->validated());
    
    // 이메일 인증 메일 발송
    $user->sendEmailVerificationNotification();
    
    return redirect('/email/verify')
        ->with('message', '인증 메일이 발송되었습니다.');
}
```

### 시나리오 2: 비밀번호 재설정 커스터마이징
```php
// 비밀번호 재설정 시 추가 검증
public function reset(Request $request)
{
    // IP 체크
    if ($this->isSuspiciousIp($request->ip())) {
        return back()->withErrors(['email' => '보안상 차단된 요청입니다.']);
    }
    
    // 비밀번호 히스토리 체크
    if ($this->isRecentlyUsedPassword($user, $request->password)) {
        return back()->withErrors(['password' => '최근 사용한 비밀번호는 사용할 수 없습니다.']);
    }
    
    // 비밀번호 재설정
    $user->password = Hash::make($request->password);
    $user->save();
}
```

### 시나리오 3: API 인증 구현
```javascript
// Vue.js에서 Sanctum 사용
async function login(email, password) {
    // CSRF 토큰 요청
    await axios.get('/sanctum/csrf-cookie');
    
    // 로그인
    await axios.post('/api/login', {
        email: email,
        password: password
    });
    
    // 인증된 요청
    const response = await axios.get('/api/user');
    console.log(response.data);
}
```

## 🚨 주의사항

1. **이메일 발송 실패**
   - 큐 시스템 사용 권장
   - 실패 시 재시도 로직 필요
   - 백업 이메일 서비스 구성

2. **토큰 관리**
   - 정기적인 만료 토큰 정리
   - 토큰 재사용 방지
   - 안전한 토큰 생성 (cryptographically secure)

3. **승인 시스템**
   - 자동 승인 시 스팸 대책 필요
   - 수동 승인 시 처리 지연 고려
   - 거부 사유 명확히 기록

4. **API Rate Limiting**
   - 과도한 요청 차단
   - IP 기반 + 사용자 기반 제한
   - 화이트리스트 관리