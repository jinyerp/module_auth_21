# 4단계: 프로필 관리 및 소셜 로그인

## 📋 개요
사용자 프로필 관리, 휴면계정 처리, 소셜 로그인 연동, 메시징 시스템 등 사용자 경험을 향상시키는 기능을 제공합니다.

## 🎯 주요 기능

### 4.1 휴면계정 관리
- 자동 휴면 전환 (장기 미접속)
- 휴면 해제 프로세스
- 휴면계정 데이터 분리 보관
- 자동 삭제 정책

### 4.2 사용자 프로필
- 프로필 사진 업로드
- 기본 정보 관리
- 추가 정보 (주소, 연락처 등)
- 프로필 변경 이력

### 4.3 소셜 로그인
- OAuth 2.0 제공자 연동
- Google, Facebook, Kakao, Naver 지원
- 계정 연결/해제
- 소셜 프로필 동기화

### 4.4 사용자 메시지
- 사용자 간 메시지 송수신
- 실시간 알림 (SSE)
- 차단 사용자 관리
- 메시지 템플릿

## 🔗 라우트 (Routes)

### 휴면계정 관리
| HTTP 메소드 | URI | 컨트롤러 메소드 | 설명 |
|------------|-----|----------------|------|
| GET | `/admin/auth/users/dormant` | AdminDormantController::index | 휴면계정 목록 |
| GET | `/admin/auth/users/dormant/statistics` | AdminDormantController::statistics | 휴면 통계 |
| POST | `/admin/auth/users/dormant/{id}/activate` | AdminDormantController::activate | 휴면 해제 |
| POST | `/admin/auth/users/dormant/{id}/delete` | AdminDormantController::delete | 휴면계정 삭제 |
| POST | `/admin/auth/users/dormant/bulk-activate` | AdminDormantController::bulkActivate | 일괄 활성화 |
| GET | `/admin/auth/users/dormant/settings` | AdminDormantController::settings | 휴면 설정 |
| POST | `/admin/auth/users/dormant/settings` | AdminDormantController::updateSettings | 설정 업데이트 |

### 사용자 프로필
| HTTP 메소드 | URI | 컨트롤러 메소드 | 설명 |
|------------|-----|----------------|------|
| GET | `/user/profile` | UserProfileController::show | 프로필 조회 |
| PUT | `/user/profile` | UserProfileController::update | 프로필 수정 |
| POST | `/user/profile/avatar` | UserProfileController::uploadAvatar | 프로필 사진 업로드 |
| DELETE | `/user/profile/avatar` | UserProfileController::deleteAvatar | 프로필 사진 삭제 |
| GET | `/user/profile/history` | UserProfileController::history | 변경 이력 |

### 관리자 프로필 관리
| HTTP 메소드 | URI | 컨트롤러 메소드 | 설명 |
|------------|-----|----------------|------|
| GET | `/admin/auth/users/{id}/profile` | AdminProfileController::show | 사용자 프로필 조회 |
| PUT | `/admin/auth/users/{id}/profile` | AdminProfileController::update | 프로필 수정 |
| POST | `/admin/auth/users/{id}/profile/avatar` | AdminProfileController::uploadAvatar | 아바타 업로드 |
| DELETE | `/admin/auth/users/{id}/profile/avatar` | AdminProfileController::deleteAvatar | 아바타 삭제 |
| GET | `/admin/auth/users/{id}/additional` | AdminProfileController::additional | 추가정보 조회 |
| PUT | `/admin/auth/users/{id}/additional` | AdminProfileController::updateAdditional | 추가정보 수정 |

### 소셜 로그인
| HTTP 메소드 | URI | 컨트롤러 메소드 | 설명 |
|------------|-----|----------------|------|
| GET | `/auth/{provider}` | SocialAuthController::redirect | 소셜 로그인 리다이렉트 |
| GET | `/auth/{provider}/callback` | SocialAuthController::callback | 소셜 로그인 콜백 |
| POST | `/user/social/{provider}/connect` | UserSocialController::connect | 소셜 계정 연결 |
| DELETE | `/user/social/{provider}/disconnect` | UserSocialController::disconnect | 소셜 계정 해제 |
| GET | `/user/social/accounts` | UserSocialController::accounts | 연결된 계정 목록 |

### 관리자 소셜 로그인 관리
| HTTP 메소드 | URI | 컨트롤러 메소드 | 설명 |
|------------|-----|----------------|------|
| GET | `/admin/auth/social` | AdminSocialController::index | 소셜 로그인 현황 |
| GET | `/admin/auth/social/statistics` | AdminSocialController::statistics | 소셜 로그인 통계 |
| GET | `/admin/auth/oauth` | AdminSocialController::oauth | OAuth 공급자 관리 |
| PUT | `/admin/auth/oauth/{id}` | AdminSocialController::updateProvider | 공급자 설정 업데이트 |
| GET | `/admin/auth/oauth/users/{provider}` | AdminSocialController::users | 제공자별 사용자 |

### 메시지 시스템
| HTTP 메소드 | URI | 컨트롤러 메소드 | 설명 |
|------------|-----|----------------|------|
| GET | `/user/messages` | UserMessageController::index | 메시지 목록 |
| POST | `/user/messages` | UserMessageController::send | 메시지 발송 |
| GET | `/user/messages/{id}` | UserMessageController::show | 메시지 상세 |
| DELETE | `/user/messages/{id}` | UserMessageController::delete | 메시지 삭제 |
| POST | `/user/messages/{id}/read` | UserMessageController::markAsRead | 읽음 표시 |
| POST | `/user/block/{userId}` | UserMessageController::blockUser | 사용자 차단 |
| DELETE | `/user/block/{userId}` | UserMessageController::unblockUser | 차단 해제 |

### 관리자 메시지 관리
| HTTP 메소드 | URI | 컨트롤러 메소드 | 설명 |
|------------|-----|----------------|------|
| GET | `/admin/auth/message` | AdminMessageController::index | 메시지 관리 |
| GET | `/admin/auth/message/compose` | AdminMessageController::compose | 메시지 작성 |
| POST | `/admin/auth/message` | AdminMessageController::send | 메시지 발송 |
| GET | `/admin/auth/message/statistics` | AdminMessageController::statistics | 메시지 통계 |
| GET | `/admin/auth/message/blocked` | AdminMessageController::blockedUsers | 차단 사용자 목록 |
| GET | `/admin/auth/message/templates` | AdminMessageController::templates | 메시지 템플릿 |

## 💾 데이터베이스 테이블

### user_profiles
| 컬럼명 | 타입 | 설명 |
|--------|------|------|
| id | BIGINT | Primary Key |
| user_id | BIGINT | 사용자 ID |
| avatar | VARCHAR | 프로필 사진 경로 |
| bio | TEXT | 자기소개 |
| phone | VARCHAR | 전화번호 |
| birth_date | DATE | 생년월일 |
| gender | ENUM | 성별 |
| address | TEXT | 주소 |
| city | VARCHAR | 도시 |
| country | VARCHAR | 국가 |
| timezone | VARCHAR | 시간대 |
| locale | VARCHAR | 언어 설정 |

### social_accounts
| 컬럼명 | 타입 | 설명 |
|--------|------|------|
| id | BIGINT | Primary Key |
| user_id | BIGINT | 사용자 ID |
| provider | VARCHAR | 제공자 (google, facebook 등) |
| provider_id | VARCHAR | 제공자 사용자 ID |
| access_token | TEXT | 액세스 토큰 (암호화) |
| refresh_token | TEXT | 리프레시 토큰 (암호화) |
| expires_at | TIMESTAMP | 토큰 만료 시간 |
| profile_data | JSON | 소셜 프로필 데이터 |

### auth_messages
| 컬럼명 | 타입 | 설명 |
|--------|------|------|
| id | BIGINT | Primary Key |
| sender_id | BIGINT | 발신자 ID |
| recipient_id | BIGINT | 수신자 ID |
| subject | VARCHAR | 제목 |
| content | TEXT | 내용 |
| type | ENUM | system/user/admin |
| priority | ENUM | low/normal/high |
| read_at | TIMESTAMP | 읽은 시간 |
| deleted_by_sender | BOOLEAN | 발신자 삭제 |
| deleted_by_recipient | BOOLEAN | 수신자 삭제 |

### user_blocks
| 컬럼명 | 타입 | 설명 |
|--------|------|------|
| id | BIGINT | Primary Key |
| user_id | BIGINT | 차단한 사용자 |
| blocked_user_id | BIGINT | 차단된 사용자 |
| reason | TEXT | 차단 사유 |
| created_at | TIMESTAMP | 차단일 |

### dormant_users
| 컬럼명 | 타입 | 설명 |
|--------|------|------|
| id | BIGINT | Primary Key |
| user_id | BIGINT | 사용자 ID |
| last_login_at | TIMESTAMP | 마지막 로그인 |
| dormant_at | TIMESTAMP | 휴면 전환일 |
| notified_at | TIMESTAMP | 알림 발송일 |
| delete_scheduled_at | TIMESTAMP | 삭제 예정일 |

## 🔌 소셜 로그인 설정

### OAuth 제공자 설정
```php
// config/services.php
'google' => [
    'client_id' => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'redirect' => env('GOOGLE_REDIRECT_URI'),
],

'facebook' => [
    'client_id' => env('FACEBOOK_CLIENT_ID'),
    'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
    'redirect' => env('FACEBOOK_REDIRECT_URI'),
],

'kakao' => [
    'client_id' => env('KAKAO_CLIENT_ID'),
    'client_secret' => env('KAKAO_CLIENT_SECRET'),
    'redirect' => env('KAKAO_REDIRECT_URI'),
],
```

### Laravel Socialite 구현
```php
use Laravel\Socialite\Facades\Socialite;

public function redirect($provider)
{
    return Socialite::driver($provider)->redirect();
}

public function callback($provider)
{
    $socialUser = Socialite::driver($provider)->user();
    
    // 기존 사용자 확인 또는 생성
    $user = User::firstOrCreate(
        ['email' => $socialUser->getEmail()],
        [
            'name' => $socialUser->getName(),
            'avatar' => $socialUser->getAvatar(),
        ]
    );
    
    // 소셜 계정 연결
    $user->socialAccounts()->updateOrCreate(
        ['provider' => $provider],
        [
            'provider_id' => $socialUser->getId(),
            'access_token' => encrypt($socialUser->token),
            'refresh_token' => encrypt($socialUser->refreshToken ?? null),
        ]
    );
    
    Auth::login($user);
    return redirect('/dashboard');
}
```

## 📦 Request/Response 예시

### 프로필 업데이트
```http
PUT /user/profile
Content-Type: application/json

{
    "name": "홍길동",
    "bio": "안녕하세요",
    "phone": "010-1234-5678",
    "birth_date": "1990-01-01",
    "gender": "male",
    "city": "Seoul",
    "country": "KR"
}
```

### 메시지 발송
```http
POST /user/messages
Content-Type: application/json

{
    "recipient_id": 123,
    "subject": "안녕하세요",
    "content": "메시지 내용입니다.",
    "priority": "normal"
}
```

### 소셜 계정 연결
```http
POST /user/social/google/connect
Authorization: Bearer {token}
```

## 🎨 프로필 사진 처리

### 업로드 및 리사이징
```php
use Intervention\Image\Facades\Image;

public function uploadAvatar(Request $request)
{
    $request->validate([
        'avatar' => 'required|image|max:2048'
    ]);
    
    $file = $request->file('avatar');
    $filename = $user->id . '_' . time() . '.jpg';
    
    // 리사이징 및 저장
    $image = Image::make($file);
    
    // 원본 저장
    $image->save(storage_path('app/public/avatars/' . $filename));
    
    // 썸네일 생성
    $image->fit(200, 200);
    $image->save(storage_path('app/public/avatars/thumb_' . $filename));
    
    // DB 업데이트
    $user->profile()->update(['avatar' => $filename]);
}
```

## 🔔 실시간 메시지 (SSE)

### Server-Sent Events 구현
```php
public function sseStream()
{
    return response()->stream(function () {
        while (true) {
            $messages = Message::where('recipient_id', auth()->id())
                ->where('read_at', null)
                ->get();
            
            if ($messages->count() > 0) {
                echo "event: message\n";
                echo "data: " . json_encode($messages) . "\n\n";
                ob_flush();
                flush();
            }
            
            sleep(3);
        }
    }, 200, [
        'Content-Type' => 'text/event-stream',
        'Cache-Control' => 'no-cache',
    ]);
}
```

### 클라이언트 구현
```javascript
const eventSource = new EventSource('/user/messages/stream');

eventSource.addEventListener('message', (event) => {
    const messages = JSON.parse(event.data);
    messages.forEach(message => {
        showNotification(message);
    });
});
```

## 🚨 주의사항

1. **휴면계정 처리**
   - 개인정보보호법 준수
   - 사전 알림 필수 (30일 전)
   - 분리 보관 및 암호화

2. **소셜 로그인 보안**
   - 토큰 암호화 저장
   - 정기적 토큰 갱신
   - 권한 최소화

3. **메시지 시스템**
   - 스팸 방지 대책
   - Rate limiting
   - 콘텐츠 필터링

4. **프로필 사진**
   - 용량 제한 (2MB)
   - 부적절한 이미지 필터링
   - CDN 활용 권장