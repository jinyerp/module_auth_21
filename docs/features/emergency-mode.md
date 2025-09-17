# 긴급 점검 모드 (Emergency Mode)

## 📋 개요
시스템 점검, 보안 이슈, 긴급 상황 발생 시 신속하게 대응할 수 있는 긴급 관리 기능입니다.

## 🎯 주요 기능

### 1. 점검 모드 (Maintenance Mode)
시스템 점검을 위해 일반 사용자의 접근을 차단하고 안내 메시지를 표시합니다.

### 2. 로그인 차단 (Login Blocking)
보안 이슈 발생 시 신규 로그인을 차단하고 기존 세션을 종료할 수 있습니다.

### 3. 긴급 알림 (Emergency Alerts)
모든 사용자 또는 특정 그룹에게 긴급 알림을 발송합니다.

### 4. 시스템 상태 점검 (System Check)
데이터베이스, 캐시, 세션, 디스크 공간 등 시스템 상태를 실시간으로 점검합니다.

### 5. 세션 강제 종료 (Kill All Sessions)
보안 침해 시 모든 사용자 세션을 즉시 종료할 수 있습니다.

## 🔗 라우트 (Routes)

| HTTP 메소드 | URI | 컨트롤러 메소드 | 설명 |
|------------|-----|----------------|------|
| GET | `/admin/auth/emergency/maintenance` | AdminEmergencyController::maintenance | 점검 모드 설정 페이지 |
| POST | `/admin/auth/emergency/maintenance` | AdminEmergencyController::toggleMaintenance | 점검 모드 토글 |
| GET | `/admin/auth/emergency/block-login` | AdminEmergencyController::blockLogin | 로그인 차단 설정 페이지 |
| POST | `/admin/auth/emergency/block-login` | AdminEmergencyController::toggleBlockLogin | 로그인 차단 토글 |
| POST | `/admin/auth/emergency/alert` | AdminEmergencyController::sendAlert | 긴급 알림 발송 |
| GET | `/admin/auth/emergency/system-check` | AdminEmergencyController::systemCheck | 시스템 상태 점검 |
| POST | `/admin/auth/emergency/kill-all-sessions` | AdminEmergencyController::killAllSessions | 모든 세션 종료 |

## 🎮 컨트롤러
**위치**: `/jiny/auth/App/Http/Controllers/Admin/AdminEmergencyController.php`

### 주요 메소드

#### toggleMaintenance(Request $request)
```php
// 요청 파라미터
'enabled' => ['required', 'boolean'],
'message' => ['required_if:enabled,true', 'string'],
'start_time' => ['nullable', 'date'],
'end_time' => ['nullable', 'date'],
'allowed_ips' => ['nullable', 'string'] // 쉼표로 구분된 IP 목록
```

#### toggleBlockLogin(Request $request)
```php
// 요청 파라미터
'enabled' => ['required', 'boolean'],
'reason' => ['required_if:enabled,true', 'string'],
'except_admins' => ['boolean'], // 관리자 제외 여부
'allowed_users' => ['nullable', 'array'] // 허용할 사용자 ID
```

#### sendAlert(Request $request)
```php
// 요청 파라미터
'type' => ['required', 'in:email,sms,both'],
'priority' => ['required', 'in:low,medium,high,critical'],
'subject' => ['required', 'string'],
'message' => ['required', 'string'],
'target' => ['required', 'in:all,admins,users,specific'],
'user_ids' => ['required_if:target,specific', 'array']
```

#### killAllSessions(Request $request)
```php
// 요청 파라미터
'except_current' => ['boolean'], // 현재 세션 제외
'admin_password' => ['required'] // 관리자 비밀번호 확인
```

## 💾 데이터베이스 테이블

### auth_maintenance_logs
점검 모드 활성화/비활성화 로그

| 컬럼명 | 타입 | 설명 |
|--------|------|------|
| id | BIGINT | Primary Key |
| action | ENUM | activated/deactivated |
| message | TEXT | 점검 메시지 |
| start_time | TIMESTAMP | 시작 시간 |
| end_time | TIMESTAMP | 종료 시간 |
| performed_by | BIGINT | 실행한 관리자 ID |

### auth_emergency_logs
긴급 상황 대응 로그

| 컬럼명 | 타입 | 설명 |
|--------|------|------|
| id | BIGINT | Primary Key |
| type | VARCHAR | login_blocked, kill_sessions 등 |
| action | VARCHAR | 수행한 작업 |
| reason | TEXT | 사유 |
| data | JSON | 추가 데이터 |
| performed_by | BIGINT | 실행한 관리자 ID |

### auth_emergency_alerts
긴급 알림 발송 기록

| 컬럼명 | 타입 | 설명 |
|--------|------|------|
| id | BIGINT | Primary Key |
| type | ENUM | email/sms/both |
| priority | ENUM | low/medium/high/critical |
| subject | VARCHAR | 제목 |
| message | TEXT | 내용 |
| target | VARCHAR | 대상 그룹 |
| sent_count | INT | 발송 건수 |
| sent_by | BIGINT | 발송한 관리자 ID |

## 📦 Request/Response 예시

### 점검 모드 활성화
```json
POST /admin/auth/emergency/maintenance
{
    "enabled": true,
    "message": "시스템 업그레이드를 위한 정기 점검 중입니다.\n점검 시간: 02:00 ~ 04:00",
    "start_time": "2025-01-20 02:00:00",
    "end_time": "2025-01-20 04:00:00",
    "allowed_ips": "192.168.1.1, 10.0.0.1"
}
```

### 시스템 상태 점검 응답
```json
{
    "success": true,
    "timestamp": "2025-01-17 15:30:00",
    "checks": {
        "database": {
            "status": "ok",
            "message": "데이터베이스 연결 정상"
        },
        "cache": {
            "status": "ok",
            "message": "캐시 시스템 정상"
        },
        "session": {
            "status": "ok",
            "message": "세션 시스템 정상"
        },
        "disk": {
            "status": "ok",
            "message": "디스크 사용률: 65.3%",
            "data": {
                "free": "34.7 GB",
                "total": "100 GB",
                "used_percent": 65.3
            }
        },
        "memory": {
            "status": "ok",
            "message": "메모리 사용량 정상",
            "data": {
                "usage": "256 MB",
                "limit": "2048M"
            }
        },
        "active_users": {
            "status": "info",
            "message": "현재 활성 사용자: 152명"
        },
        "errors": {
            "status": "ok",
            "message": "최근 1시간 에러: 3건"
        }
    }
}
```

## 🔒 보안 고려사항

1. **관리자 권한 필수**
   - 모든 긴급 대응 기능은 관리자 권한 필요
   - 중요 작업은 관리자 비밀번호 재확인

2. **IP 화이트리스트**
   - 점검 모드에서도 특정 IP는 접근 허용
   - 관리자 IP는 자동으로 화이트리스트에 추가

3. **캐시 기반 작동**
   - 점검/차단 모드는 캐시에 저장
   - DB 장애 시에도 작동 가능

4. **Laravel 호환**
   - Laravel의 기본 점검 모드와 호환
   - `php artisan down` 명령과 동일한 효과

## 🎨 뷰 파일

### 점검 모드 설정 페이지
**위치**: `/jiny/auth/resources/views/admin/emergency/maintenance.blade.php`

주요 요소:
- 점검 모드 ON/OFF 토글 스위치
- 점검 메시지 입력 필드
- 시작/종료 시간 설정
- 허용 IP 목록 관리

### 로그인 차단 설정 페이지
**위치**: `/jiny/auth/resources/views/admin/emergency/block-login.blade.php`

주요 요소:
- 로그인 차단 ON/OFF 토글
- 차단 사유 입력
- 관리자 제외 옵션
- 특정 사용자 허용 목록

## ⚙️ 설정

### 캐시 저장 시간
- 점검 모드: 24시간 (86400초)
- 로그인 차단: 24시간 (86400초)

### 점검 모드 파일 위치
```php
storage_path('framework/down')
```

## 📝 활용 예시

### 시나리오 1: 정기 점검
```javascript
// 새벽 2시-4시 정기 점검 설정
fetch('/admin/auth/emergency/maintenance', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken
    },
    body: JSON.stringify({
        enabled: true,
        message: '정기 시스템 점검 중입니다.\n점검 시간: 02:00 ~ 04:00\n이용에 불편을 드려 죄송합니다.',
        start_time: '2025-01-20 02:00:00',
        end_time: '2025-01-20 04:00:00',
        allowed_ips: adminIpList
    })
});
```

### 시나리오 2: 보안 침해 대응
```javascript
// 1단계: 로그인 차단
fetch('/admin/auth/emergency/block-login', {
    method: 'POST',
    body: JSON.stringify({
        enabled: true,
        reason: '보안 점검 중',
        except_admins: true
    })
});

// 2단계: 모든 세션 종료
fetch('/admin/auth/emergency/kill-all-sessions', {
    method: 'POST',
    body: JSON.stringify({
        except_current: true,
        admin_password: adminPassword
    })
});

// 3단계: 긴급 알림 발송
fetch('/admin/auth/emergency/alert', {
    method: 'POST',
    body: JSON.stringify({
        type: 'both',
        priority: 'critical',
        subject: '보안 알림',
        message: '보안 점검을 위해 재로그인이 필요합니다.',
        target: 'all'
    })
});
```

## 🚨 주의사항

1. **점검 모드 해제**
   - 점검 완료 후 반드시 수동으로 해제
   - end_time이 지나도 자동 해제되지 않음

2. **세션 종료 영향**
   - 관리자 포함 모든 세션 종료 가능
   - 현재 세션 제외 옵션 활용 권장

3. **알림 발송 부하**
   - 대량 알림은 큐를 통해 처리
   - SMS는 비용 발생 주의

4. **복구 절차**
   - 모든 긴급 작업은 로그에 기록
   - 문제 발생 시 로그 기반 복구 가능

## 🔄 자동화 가능 작업

1. **정기 점검 스케줄링**
   - Laravel 스케줄러로 자동 점검 모드 설정
   - Cron job으로 정기 실행

2. **모니터링 연동**
   - 시스템 상태 이상 시 자동 점검 모드 전환
   - 특정 에러 임계치 도달 시 알림 발송

3. **보안 자동 대응**
   - 무차별 대입 공격 감지 시 로그인 차단
   - 의심스러운 활동 감지 시 세션 종료