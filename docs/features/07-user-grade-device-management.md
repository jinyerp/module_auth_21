# 7단계: 회원 등급 및 디바이스 관리

## 📋 개요
회원 등급 시스템을 통한 차별화된 서비스 제공과 사용자의 디바이스 접속 관리를 통한 보안 강화 기능을 제공합니다.

## 🎯 주요 기능

### 7.1 회원 등급 시스템
- 등급 생성/수정/삭제
- 자동 등급 승급/강등
- 등급별 혜택 관리
- 등급 변경 이력

### 7.2 등급 혜택
- 포인트 적립률 차등
- 할인율 적용
- 전용 서비스 제공
- 이벤트 우선 참여

### 7.3 디바이스 관리
- 디바이스 등록/인증
- 신뢰 디바이스 관리
- 디바이스별 접속 이력
- 이상 디바이스 감지

### 7.4 접속 제한
- 동시 접속 디바이스 수 제한
- 디바이스별 접속 차단
- 지역별 접속 제한
- 디바이스 화이트리스트

## 🔗 라우트 (Routes)

### 회원 등급 관리
| HTTP 메소드 | URI | 컨트롤러 메소드 | 설명 |
|------------|-----|----------------|------|
| GET | `/admin/auth/grades` | AdminUserGradeController::index | 등급 목록 |
| GET | `/admin/auth/grades/create` | AdminUserGradeController::create | 등급 생성 폼 |
| POST | `/admin/auth/grades` | AdminUserGradeController::store | 등급 저장 |
| GET | `/admin/auth/grades/{id}/edit` | AdminUserGradeController::edit | 등급 수정 폼 |
| PUT | `/admin/auth/grades/{id}` | AdminUserGradeController::update | 등급 업데이트 |
| DELETE | `/admin/auth/grades/{id}` | AdminUserGradeController::destroy | 등급 삭제 |
| GET | `/admin/auth/grades/{id}/users` | AdminUserGradeController::users | 해당 등급 회원 목록 |

### 등급 혜택 관리
| HTTP 메소드 | URI | 컨트롤러 메소드 | 설명 |
|------------|-----|----------------|------|
| GET | `/admin/auth/grades/{id}/benefits` | AdminGradeBenefitController::index | 혜택 목록 |
| POST | `/admin/auth/grades/{id}/benefits` | AdminGradeBenefitController::store | 혜택 추가 |
| PUT | `/admin/auth/benefits/{id}` | AdminGradeBenefitController::update | 혜택 수정 |
| DELETE | `/admin/auth/benefits/{id}` | AdminGradeBenefitController::destroy | 혜택 삭제 |

### 등급 변경 관리
| HTTP 메소드 | URI | 컨트롤러 메소드 | 설명 |
|------------|-----|----------------|------|
| POST | `/admin/auth/users/{id}/grade` | AdminUserGradeController::changeGrade | 수동 등급 변경 |
| GET | `/admin/auth/grade-history` | AdminUserGradeController::history | 등급 변경 이력 |
| POST | `/admin/auth/grades/auto-upgrade` | AdminUserGradeController::autoUpgrade | 자동 승급 실행 |

### 디바이스 관리 (사용자)
| HTTP 메소드 | URI | 컨트롤러 메소드 | 설명 |
|------------|-----|----------------|------|
| GET | `/user/devices` | UserDeviceController::index | 내 디바이스 목록 |
| POST | `/user/devices/{id}/trust` | UserDeviceController::trust | 신뢰 디바이스 등록 |
| POST | `/user/devices/{id}/rename` | UserDeviceController::rename | 디바이스명 변경 |
| DELETE | `/user/devices/{id}` | UserDeviceController::remove | 디바이스 제거 |
| POST | `/user/devices/verify` | UserDeviceController::verify | 디바이스 인증 |

### 디바이스 관리 (관리자)
| HTTP 메소드 | URI | 컨트롤러 메소드 | 설명 |
|------------|-----|----------------|------|
| GET | `/admin/auth/devices` | AdminDeviceController::index | 전체 디바이스 목록 |
| GET | `/admin/auth/devices/statistics` | AdminDeviceController::statistics | 디바이스 통계 |
| GET | `/admin/auth/users/{id}/devices` | AdminDeviceController::userDevices | 사용자별 디바이스 |
| POST | `/admin/auth/devices/{id}/block` | AdminDeviceController::block | 디바이스 차단 |
| POST | `/admin/auth/devices/{id}/unblock` | AdminDeviceController::unblock | 차단 해제 |
| GET | `/admin/auth/devices/suspicious` | AdminDeviceController::suspicious | 의심스러운 디바이스 |

## 🎮 컨트롤러

### AdminUserGradeController
**위치**: `/jiny/auth/App/Http/Controllers/Admin/AdminUserGradeController.php`

#### store(Request $request)
```php
// 요청 파라미터
'name' => ['required', 'string', 'max:50'],
'level' => ['required', 'integer', 'unique:user_grades'],
'min_points' => ['nullable', 'integer'],
'min_purchases' => ['nullable', 'integer'],
'benefits' => ['nullable', 'array']

// 처리
1. 등급 생성
2. 혜택 설정
3. 자동 승급 조건 설정
4. 캐시 업데이트
```

### UserDeviceController
**위치**: `/jiny/auth/App/Http/Controllers/UserDeviceController.php`

#### verify(Request $request)
```php
// 요청 파라미터
'device_id' => ['required', 'string'],
'verification_code' => ['required', 'string']

// 처리
1. 디바이스 정보 확인
2. 인증 코드 검증
3. 디바이스 등록
4. 신뢰도 설정
```

## 💾 데이터베이스 테이블

### user_grades
| 컬럼명 | 타입 | 설명 |
|--------|------|------|
| id | BIGINT | Primary Key |
| name | VARCHAR | 등급명 |
| level | INT | 등급 레벨 |
| icon | VARCHAR | 등급 아이콘 |
| color | VARCHAR | 등급 색상 |
| min_points | INT | 최소 필요 포인트 |
| min_purchases | INT | 최소 구매 횟수 |
| min_amount | DECIMAL | 최소 구매 금액 |
| keep_months | INT | 유지 기간 (개월) |
| is_active | BOOLEAN | 활성화 여부 |

### grade_benefits
| 컬럼명 | 타입 | 설명 |
|--------|------|------|
| id | BIGINT | Primary Key |
| grade_id | BIGINT | 등급 ID |
| type | VARCHAR | 혜택 유형 |
| name | VARCHAR | 혜택명 |
| value | VARCHAR | 혜택 값 |
| description | TEXT | 설명 |

### user_grade_history
| 컬럼명 | 타입 | 설명 |
|--------|------|------|
| id | BIGINT | Primary Key |
| user_id | BIGINT | 사용자 ID |
| from_grade_id | BIGINT | 이전 등급 |
| to_grade_id | BIGINT | 변경 등급 |
| reason | VARCHAR | 변경 사유 |
| changed_by | BIGINT | 변경자 (NULL=자동) |
| created_at | TIMESTAMP | 변경일 |

### user_devices
| 컬럼명 | 타입 | 설명 |
|--------|------|------|
| id | BIGINT | Primary Key |
| user_id | BIGINT | 사용자 ID |
| device_id | VARCHAR | 디바이스 ID (Unique) |
| device_name | VARCHAR | 디바이스명 |
| device_type | VARCHAR | PC/Mobile/Tablet |
| os | VARCHAR | 운영체제 |
| browser | VARCHAR | 브라우저 |
| ip_address | VARCHAR | IP 주소 |
| is_trusted | BOOLEAN | 신뢰 디바이스 |
| is_blocked | BOOLEAN | 차단 여부 |
| last_used_at | TIMESTAMP | 마지막 사용 |
| verified_at | TIMESTAMP | 인증일 |

### device_access_logs
| 컬럼명 | 타입 | 설명 |
|--------|------|------|
| id | BIGINT | Primary Key |
| device_id | VARCHAR | 디바이스 ID |
| user_id | BIGINT | 사용자 ID |
| ip_address | VARCHAR | IP 주소 |
| location | VARCHAR | 접속 위치 |
| action | VARCHAR | 액션 (login/logout/action) |
| success | BOOLEAN | 성공 여부 |
| created_at | TIMESTAMP | 접속 시간 |

## 🎖 회원 등급 시스템

### 등급 체계 예시
```php
// config/user_grades.php
return [
    'grades' => [
        [
            'name' => 'Bronze',
            'level' => 1,
            'min_points' => 0,
            'benefits' => [
                'point_rate' => 1.0,
                'discount_rate' => 0
            ]
        ],
        [
            'name' => 'Silver', 
            'level' => 2,
            'min_points' => 1000,
            'min_purchases' => 5,
            'benefits' => [
                'point_rate' => 1.5,
                'discount_rate' => 5
            ]
        ],
        [
            'name' => 'Gold',
            'level' => 3,
            'min_points' => 5000,
            'min_purchases' => 20,
            'min_amount' => 500000,
            'benefits' => [
                'point_rate' => 2.0,
                'discount_rate' => 10,
                'free_shipping' => true
            ]
        ],
        [
            'name' => 'Platinum',
            'level' => 4,
            'min_points' => 20000,
            'min_purchases' => 50,
            'min_amount' => 2000000,
            'benefits' => [
                'point_rate' => 3.0,
                'discount_rate' => 15,
                'free_shipping' => true,
                'priority_support' => true
            ]
        ]
    ]
];
```

### 자동 등급 승급
```php
// app/Services/UserGradeService.php
public function checkAndUpgrade(User $user)
{
    $currentGrade = $user->grade;
    $stats = $this->getUserStats($user);
    
    // 다음 등급 확인
    $nextGrade = UserGrade::where('level', '>', $currentGrade->level)
        ->orderBy('level')
        ->first();
    
    if (!$nextGrade) {
        return; // 최고 등급
    }
    
    // 승급 조건 체크
    $eligible = true;
    
    if ($nextGrade->min_points && $stats['total_points'] < $nextGrade->min_points) {
        $eligible = false;
    }
    
    if ($nextGrade->min_purchases && $stats['purchase_count'] < $nextGrade->min_purchases) {
        $eligible = false;
    }
    
    if ($nextGrade->min_amount && $stats['total_amount'] < $nextGrade->min_amount) {
        $eligible = false;
    }
    
    if ($eligible) {
        $this->upgradeUser($user, $nextGrade, 'auto_upgrade');
    }
}
```

### 등급 혜택 적용
```php
// 포인트 적립 시 등급별 적립률 적용
public function calculatePoints($user, $basePoints)
{
    $grade = $user->grade;
    $benefit = $grade->benefits()->where('type', 'point_rate')->first();
    
    $rate = $benefit ? $benefit->value : 1.0;
    
    return floor($basePoints * $rate);
}

// 구매 시 등급별 할인 적용
public function applyGradeDiscount($user, $price)
{
    $grade = $user->grade;
    $benefit = $grade->benefits()->where('type', 'discount_rate')->first();
    
    if (!$benefit) {
        return $price;
    }
    
    $discountRate = $benefit->value / 100;
    $discount = $price * $discountRate;
    
    return $price - $discount;
}
```

## 📱 디바이스 관리

### 디바이스 등록 및 인증
```php
// app/Services/DeviceService.php
public function registerDevice(Request $request, User $user)
{
    $deviceId = $this->generateDeviceId($request);
    
    $device = UserDevice::firstOrCreate(
        [
            'user_id' => $user->id,
            'device_id' => $deviceId
        ],
        [
            'device_name' => $this->detectDeviceName($request),
            'device_type' => $this->detectDeviceType($request),
            'os' => $this->detectOS($request),
            'browser' => $this->detectBrowser($request),
            'ip_address' => $request->ip()
        ]
    );
    
    // 새 디바이스인 경우 인증 요청
    if ($device->wasRecentlyCreated) {
        $this->sendDeviceVerification($user, $device);
    }
    
    return $device;
}

private function generateDeviceId(Request $request)
{
    $fingerprint = [
        $request->header('User-Agent'),
        $request->header('Accept-Language'),
        $request->header('Accept-Encoding'),
        $request->ip()
    ];
    
    return hash('sha256', implode('|', $fingerprint));
}
```

### 디바이스 신뢰도 관리
```php
public function trustDevice(UserDevice $device, $verificationCode)
{
    // 인증 코드 확인
    $storedCode = Cache::get("device_verify_{$device->id}");
    
    if ($storedCode !== $verificationCode) {
        throw new \Exception('Invalid verification code');
    }
    
    // 디바이스 신뢰 설정
    $device->update([
        'is_trusted' => true,
        'verified_at' => now()
    ]);
    
    // 인증 코드 삭제
    Cache::forget("device_verify_{$device->id}");
    
    // 30일간 재인증 불필요
    Cookie::queue('trusted_device', encrypt($device->device_id), 43200);
    
    return $device;
}
```

### 이상 디바이스 감지
```php
public function detectSuspiciousDevice($device, $request)
{
    $suspicious = false;
    $reasons = [];
    
    // 1. 위치 이상 감지
    $lastLocation = $device->accessLogs()->latest()->first();
    if ($lastLocation) {
        $distance = $this->calculateDistance(
            $lastLocation->location,
            $request->location
        );
        
        $timeDiff = now()->diffInHours($lastLocation->created_at);
        
        // 물리적으로 불가능한 이동
        if ($distance > 1000 && $timeDiff < 2) {
            $suspicious = true;
            $reasons[] = 'impossible_travel';
        }
    }
    
    // 2. 다중 계정 감지
    $otherUsers = UserDevice::where('device_id', $device->device_id)
        ->where('user_id', '!=', $device->user_id)
        ->count();
    
    if ($otherUsers > 2) {
        $suspicious = true;
        $reasons[] = 'multiple_accounts';
    }
    
    // 3. 짧은 시간 내 다수 로그인 시도
    $recentAttempts = DeviceAccessLog::where('device_id', $device->device_id)
        ->where('created_at', '>', now()->subMinutes(5))
        ->where('success', false)
        ->count();
    
    if ($recentAttempts > 5) {
        $suspicious = true;
        $reasons[] = 'brute_force';
    }
    
    if ($suspicious) {
        $this->handleSuspiciousDevice($device, $reasons);
    }
    
    return $suspicious;
}
```

## 📦 Request/Response 예시

### 등급 생성
```http
POST /admin/auth/grades
Content-Type: application/json

{
    "name": "VIP",
    "level": 5,
    "min_points": 50000,
    "min_purchases": 100,
    "min_amount": 5000000,
    "benefits": [
        {"type": "point_rate", "value": "5.0"},
        {"type": "discount_rate", "value": "20"},
        {"type": "free_shipping", "value": "true"},
        {"type": "priority_support", "value": "true"},
        {"type": "exclusive_events", "value": "true"}
    ]
}
```

### 디바이스 인증
```http
POST /user/devices/verify
Content-Type: application/json

{
    "device_id": "abc123def456",
    "verification_code": "123456"
}
```

응답:
```json
{
    "success": true,
    "device": {
        "id": 1,
        "device_name": "Chrome on Windows",
        "is_trusted": true,
        "verified_at": "2025-01-17 10:00:00"
    }
}
```

## 🔒 보안 고려사항

### 디바이스 제한
```php
// 동시 접속 디바이스 수 제한
public function checkDeviceLimit($user)
{
    $maxDevices = $user->grade->max_devices ?? 5;
    
    $activeDevices = $user->devices()
        ->where('last_used_at', '>', now()->subDays(30))
        ->count();
    
    if ($activeDevices >= $maxDevices) {
        // 가장 오래된 디바이스 자동 로그아웃
        $oldestDevice = $user->devices()
            ->orderBy('last_used_at')
            ->first();
        
        $this->logoutDevice($oldestDevice);
    }
}
```

### 지역 제한
```php
// IP 기반 국가 차단
public function checkCountryRestriction($request, $user)
{
    $country = geoip($request->ip())->country;
    
    // 사용자별 허용 국가
    $allowedCountries = $user->allowed_countries ?? ['KR', 'US', 'JP'];
    
    if (!in_array($country, $allowedCountries)) {
        throw new \Exception('Access denied from this country');
    }
}
```

## 🚨 주의사항

1. **등급 시스템**
   - 강등 정책 명확히 정의
   - 등급 변경 시 알림 필수
   - 혜택 변경 사전 공지

2. **디바이스 관리**
   - 개인정보 최소 수집
   - 디바이스 정보 암호화
   - 정기적 미사용 디바이스 정리

3. **접속 제한**
   - 정당한 사용자 차단 방지
   - 오탐 시 복구 프로세스
   - VPN 사용자 고려

## 📝 활용 예시

### 시나리오 1: 등급별 이벤트 참여
```php
public function checkEventEligibility($user, $event)
{
    $requiredGrade = $event->minimum_grade_level;
    
    if ($user->grade->level < $requiredGrade) {
        return [
            'eligible' => false,
            'message' => "{$event->minimum_grade->name} 등급 이상만 참여 가능합니다."
        ];
    }
    
    return ['eligible' => true];
}
```

### 시나리오 2: 신규 디바이스 알림
```php
// 새 디바이스 로그인 감지
event(new NewDeviceLogin($user, $device));

// EventListener
public function handle(NewDeviceLogin $event)
{
    // 이메일 알림
    Mail::to($event->user)->send(new NewDeviceAlert([
        'device' => $event->device,
        'location' => geoip($event->device->ip_address)->city,
        'time' => now()
    ]));
    
    // SMS 알림 (중요 계정)
    if ($event->user->grade->level >= 3) {
        SMS::send($event->user->phone, 
            "새로운 기기에서 로그인이 감지되었습니다. 본인이 아닌 경우 즉시 비밀번호를 변경하세요."
        );
    }
}
```