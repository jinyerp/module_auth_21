# 3단계: 2FA 및 고급 보안 기능

## 📋 개요
2단계 인증(2FA), 세션 관리, 블랙리스트, JWT 토큰 관리 등 고급 보안 기능을 제공합니다.

## 🎯 주요 기능

### 3.1 2단계 인증 (2FA)
- TOTP(Time-based One-Time Password) 지원
- SMS OTP 지원
- 백업 코드 제공
- QR 코드 설정

### 3.2 세션 관리
- 활성 세션 조회
- 원격 세션 종료
- 디바이스별 세션 추적
- 동시 로그인 제한

### 3.3 블랙리스트 관리
- IP 차단/허용 목록
- 이메일 도메인 차단
- 사용자 차단 관리
- 자동 차단 규칙

### 3.4 JWT 토큰 관리
- 토큰 발급/취소
- 토큰 갱신
- 블랙리스트 토큰
- 토큰 권한 관리

## 🔗 라우트 (Routes)

### 2FA 설정 (사용자)
| HTTP 메소드 | URI | 컨트롤러 메소드 | 설명 |
|------------|-----|----------------|------|
| GET | `/user/2fa` | User2FAController::index | 2FA 설정 페이지 |
| POST | `/user/2fa/enable` | User2FAController::enable | 2FA 활성화 |
| POST | `/user/2fa/disable` | User2FAController::disable | 2FA 비활성화 |
| POST | `/user/2fa/verify` | User2FAController::verify | 2FA 코드 검증 |
| GET | `/user/2fa/recovery` | User2FAController::showRecoveryCodes | 복구 코드 표시 |
| POST | `/user/2fa/recovery` | User2FAController::regenerateRecoveryCodes | 복구 코드 재생성 |

### 2FA 관리 (관리자)
| HTTP 메소드 | URI | 컨트롤러 메소드 | 설명 |
|------------|-----|----------------|------|
| GET | `/admin/auth/2fa/settings` | Admin2FAController::settings | 2FA 설정 |
| POST | `/admin/auth/2fa/settings` | Admin2FAController::updateSettings | 설정 업데이트 |
| GET | `/admin/auth/2fa/users` | Admin2FAController::users | 2FA 사용자 목록 |
| POST | `/admin/auth/2fa/users/{id}/disable` | Admin2FAController::disableUser | 사용자 2FA 비활성화 |
| POST | `/admin/auth/2fa/users/{id}/force-enable` | Admin2FAController::forceEnableUser | 2FA 강제 활성화 |
| GET | `/admin/auth/2fa/statistics` | Admin2FAController::statistics | 2FA 통계 |

### 세션 관리 (사용자)
| HTTP 메소드 | URI | 컨트롤러 메소드 | 설명 |
|------------|-----|----------------|------|
| GET | `/user/sessions` | UserSessionController::index | 활성 세션 목록 |
| POST | `/user/sessions/{id}/terminate` | UserSessionController::terminate | 특정 세션 종료 |
| POST | `/user/sessions/terminate-all` | UserSessionController::terminateAll | 모든 세션 종료 |

### 세션 관리 (관리자)
| HTTP 메소드 | URI | 컨트롤러 메소드 | 설명 |
|------------|-----|----------------|------|
| GET | `/admin/auth/sessions` | AdminSessionController::index | 전체 세션 목록 |
| GET | `/admin/auth/sessions/{id}/details` | AdminSessionController::details | 세션 상세 정보 |
| POST | `/admin/auth/sessions/{id}/terminate` | AdminSessionController::terminate | 세션 강제 종료 |
| POST | `/admin/auth/sessions/bulk-terminate` | AdminSessionController::bulkTerminate | 대량 세션 종료 |
| GET | `/admin/auth/sessions/statistics` | AdminSessionController::statistics | 세션 통계 |

### 블랙리스트 관리
| HTTP 메소드 | URI | 컨트롤러 메소드 | 설명 |
|------------|-----|----------------|------|
| GET | `/admin/auth/blacklist` | AdminBlacklistController::index | 블랙리스트 목록 |
| GET | `/admin/auth/blacklist/email` | AdminBlacklistController::emailList | 이메일 블랙리스트 |
| GET | `/admin/auth/blacklist/ip` | AdminBlacklistController::ipList | IP 블랙리스트 |
| POST | `/admin/auth/blacklist/email` | AdminBlacklistController::addEmail | 이메일 추가 |
| POST | `/admin/auth/blacklist/ip` | AdminBlacklistController::addIp | IP 추가 |
| DELETE | `/admin/auth/blacklist/{id}` | AdminBlacklistController::destroy | 항목 삭제 |
| GET | `/admin/auth/blacklist/whitelist` | AdminBlacklistController::whitelist | 화이트리스트 |

### JWT 토큰 관리
| HTTP 메소드 | URI | 컨트롤러 메소드 | 설명 |
|------------|-----|----------------|------|
| GET | `/admin/auth/jwt/tokens` | AdminJWTController::index | 토큰 목록 |
| GET | `/admin/auth/jwt/tokens/active` | AdminJWTController::active | 활성 토큰 |
| GET | `/admin/auth/jwt/tokens/expired` | AdminJWTController::expired | 만료 토큰 |
| DELETE | `/admin/auth/jwt/tokens/{id}` | AdminJWTController::destroy | 토큰 삭제 |
| POST | `/admin/auth/jwt/tokens/revoke-all` | AdminJWTController::revokeAll | 모든 토큰 취소 |
| POST | `/admin/auth/jwt/tokens/revoke-user/{id}` | AdminJWTController::revokeUser | 사용자 토큰 취소 |
| GET | `/admin/auth/jwt/statistics` | AdminJWTController::statistics | 토큰 통계 |

## 🎮 컨트롤러

### User2FAController
**위치**: `/jiny/auth/App/Http/Controllers/User2FAController.php`

#### enable(Request $request)
```php
// 2FA 활성화 프로세스
1. 비밀키 생성 (Google2FA)
2. QR 코드 생성
3. 사용자 확인 코드 검증
4. 백업 코드 생성 (8개)
5. DB 저장 및 활성화
```

### AdminSessionController
**위치**: `/jiny/auth/App/Http/Controllers/Admin/AdminSessionController.php`

#### bulkTerminate(Request $request)
```php
// 요청 파라미터
'session_ids' => ['required', 'array'],
'reason' => ['required', 'string']

// 처리
1. 선택된 세션 종료
2. 사용자에게 알림
3. 로그 기록
```

## 💾 데이터베이스 테이블

### auth_user2fas
| 컬럼명 | 타입 | 설명 |
|--------|------|------|
| id | BIGINT | Primary Key |
| user_id | BIGINT | 사용자 ID |
| secret | VARCHAR | TOTP 비밀키 |
| recovery_codes | TEXT | 백업 코드 (암호화) |
| enabled | BOOLEAN | 활성화 여부 |
| last_used_at | TIMESTAMP | 마지막 사용 시간 |
| created_at | TIMESTAMP | 생성일 |

### auth_user_sessions
| 컬럼명 | 타입 | 설명 |
|--------|------|------|
| id | BIGINT | Primary Key |
| user_id | BIGINT | 사용자 ID |
| session_id | VARCHAR | 세션 ID |
| ip_address | VARCHAR | IP 주소 |
| user_agent | TEXT | 브라우저 정보 |
| device_type | VARCHAR | 디바이스 유형 |
| location | VARCHAR | 위치 정보 |
| last_activity | TIMESTAMP | 마지막 활동 |
| logged_out_at | TIMESTAMP | 로그아웃 시간 |

### auth_blacklists
| 컬럼명 | 타입 | 설명 |
|--------|------|------|
| id | BIGINT | Primary Key |
| type | ENUM | email/ip/user |
| value | VARCHAR | 차단 값 |
| reason | TEXT | 차단 사유 |
| expires_at | TIMESTAMP | 만료 시간 |
| created_by | BIGINT | 생성자 ID |

### jwt_blacklists
| 컬럼명 | 타입 | 설명 |
|--------|------|------|
| id | BIGINT | Primary Key |
| jti | VARCHAR | JWT ID (Unique) |
| user_id | BIGINT | 사용자 ID |
| revoked_at | TIMESTAMP | 취소 시간 |
| expires_at | TIMESTAMP | 토큰 만료 시간 |

## 🔐 2FA 구현

### TOTP 설정
```php
use PragmaRX\Google2FA\Google2FA;

$google2fa = new Google2FA();

// 비밀키 생성
$secret = $google2fa->generateSecretKey();

// QR 코드 URL 생성
$qrCodeUrl = $google2fa->getQRCodeUrl(
    config('app.name'),
    $user->email,
    $secret
);

// 코드 검증
$valid = $google2fa->verifyKey($secret, $request->code);
```

### 백업 코드 생성
```php
// 8개의 백업 코드 생성
$recoveryCodes = [];
for ($i = 0; $i < 8; $i++) {
    $recoveryCodes[] = Str::random(10) . '-' . Str::random(10);
}

// 암호화하여 저장
$encrypted = encrypt($recoveryCodes);
```

## 📦 Request/Response 예시

### 2FA 활성화
```http
POST /user/2fa/enable
Content-Type: application/json

{
    "code": "123456",
    "password": "current_password"
}
```

응답:
```json
{
    "success": true,
    "recovery_codes": [
        "ABCD1234-EFGH5678",
        "IJKL9012-MNOP3456",
        // ... 6 more
    ]
}
```

### 세션 정보 조회
```http
GET /user/sessions
```

응답:
```json
{
    "sessions": [
        {
            "id": "sess_123",
            "device": "Chrome on Windows",
            "ip_address": "192.168.1.1",
            "location": "Seoul, KR",
            "last_activity": "2025-01-17 10:30:00",
            "current": true
        }
    ]
}
```

## 🔒 보안 고려사항

### 2FA 보안
1. **백업 코드**
   - 암호화 저장
   - 일회용 사용
   - 안전한 표시 (한 번만)

2. **TOTP 설정**
   - 30초 시간 창
   - 이전/다음 시간 창 허용
   - 재사용 방지

3. **강제 적용**
   - 관리자 계정 필수
   - 민감한 작업 시 재인증

### 세션 보안
1. **세션 하이재킹 방지**
   - IP 변경 감지
   - User-Agent 검증
   - 지문(Fingerprint) 추적

2. **동시 로그인 제한**
   - 디바이스 수 제한
   - 새 로그인 시 알림
   - 의심스러운 활동 차단

### 블랙리스트 관리
1. **자동 차단**
   - 실패 횟수 초과
   - 의심스러운 패턴
   - 봇 감지

2. **임시 차단**
   - 시간 기반 차단
   - 자동 해제
   - 에스컬레이션

## 📝 활용 예시

### 시나리오 1: 2FA 로그인 플로우
```php
public function login(Request $request)
{
    $credentials = $request->only('email', 'password');
    
    if (Auth::attempt($credentials)) {
        $user = Auth::user();
        
        // 2FA 활성화 체크
        if ($user->has2FAEnabled()) {
            // 2FA 페이지로 리다이렉트
            session(['2fa:user:id' => $user->id]);
            Auth::logout();
            return redirect('/2fa/verify');
        }
        
        return redirect('/dashboard');
    }
    
    return back()->withErrors(['email' => 'Invalid credentials']);
}
```

### 시나리오 2: 세션 모니터링
```javascript
// 실시간 세션 모니터링
setInterval(async () => {
    const sessions = await fetch('/api/user/sessions');
    const data = await sessions.json();
    
    // 새로운 세션 감지
    const newSession = data.sessions.find(s => !knownSessions.includes(s.id));
    if (newSession && !newSession.current) {
        if (confirm('새로운 로그인이 감지되었습니다. 차단하시겠습니까?')) {
            await fetch(`/api/user/sessions/${newSession.id}/terminate`, {
                method: 'POST'
            });
        }
    }
}, 60000); // 1분마다 체크
```

### 시나리오 3: 자동 블랙리스트
```php
// 로그인 실패 시 자동 차단
public function handleFailedLogin($email, $ip)
{
    $attempts = Cache::increment("login_attempts:{$ip}", 1);
    
    if ($attempts > 5) {
        // IP 블랙리스트 추가
        DB::table('auth_blacklists')->insert([
            'type' => 'ip',
            'value' => $ip,
            'reason' => '로그인 시도 초과',
            'expires_at' => now()->addHours(1),
            'created_by' => null // 시스템 자동
        ]);
        
        // 알림
        event(new SuspiciousActivityDetected($ip, $email));
    }
}
```

## 🚨 주의사항

1. **2FA 복구**
   - 백업 코드 분실 대비
   - 관리자 해제 프로세스
   - 신원 확인 절차

2. **세션 관리**
   - 정기적 세션 정리
   - 좀비 세션 방지
   - 메모리 사용량 모니터링

3. **블랙리스트 오남용**
   - 정당한 사용자 차단 방지
   - 화이트리스트 우선
   - 정기적 검토

4. **JWT 보안**
   - 짧은 만료 시간
   - Refresh 토큰 분리
   - 취소 목록 관리