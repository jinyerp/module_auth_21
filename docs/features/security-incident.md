# 보안 사고 대응 (Security Incident Response)

## 📋 개요
보안 사고 발생 시 체계적으로 대응하고 관리할 수 있는 통합 보안 사고 관리 시스템입니다.

## 🎯 주요 기능

### 1. 사고 등록 및 분류
- 보안 사고를 유형별(침해, 공격, 취약점, 의심 활동)로 분류
- 심각도 레벨(낮음, 중간, 높음, 치명적) 설정
- 영향받은 사용자 및 시스템 기록

### 2. 자동 대응 시스템
- 심각도에 따른 자동 보안 조치
- 세션 종료, 비밀번호 재설정, CAPTCHA 활성화 등

### 3. 사고 조사 및 추적
- 타임라인 기반 사고 진행 추적
- 조치 내역 상세 기록
- 담당자별 활동 로그

### 4. 사고 해결 및 보고
- 근본 원인 분석
- 예방 조치 수립
- 자동 보고서 생성

## 🔗 라우트 (Routes)

| HTTP 메소드 | URI | 컨트롤러 메소드 | 설명 |
|------------|-----|----------------|------|
| GET | `/admin/auth/security-incident` | AdminSecurityIncidentController::index | 사고 목록 조회 |
| POST | `/admin/auth/security-incident` | AdminSecurityIncidentController::store | 새 사고 등록 |
| GET | `/admin/auth/security-incident/{id}` | AdminSecurityIncidentController::show | 사고 상세 조회 |
| PUT | `/admin/auth/security-incident/{id}` | AdminSecurityIncidentController::update | 사고 정보 업데이트 |
| POST | `/admin/auth/security-incident/{id}/resolve` | AdminSecurityIncidentController::resolve | 사고 해결 처리 |
| POST | `/admin/auth/security-incident/{id}/action` | AdminSecurityIncidentController::addAction | 조치 사항 추가 |

## 🎮 컨트롤러
**위치**: `/jiny/auth/App/Http/Controllers/Admin/AdminSecurityIncidentController.php`

### 주요 메소드

#### store(Request $request)
```php
// 요청 파라미터
'title' => ['required', 'string'],
'type' => ['required', 'in:breach,attack,vulnerability,suspicious,other'],
'severity' => ['required', 'in:low,medium,high,critical'],
'description' => ['required', 'string'],
'affected_users' => ['nullable', 'array'],
'affected_systems' => ['nullable', 'array'],
'immediate_action' => ['nullable', 'string']
```

#### update(Request $request, $id)
```php
// 요청 파라미터
'status' => ['required', 'in:open,investigating,contained,resolved,closed'],
'severity' => ['required', 'in:low,medium,high,critical'],
'update_note' => ['required', 'string']
```

#### resolve(Request $request, $id)
```php
// 요청 파라미터
'resolution' => ['required', 'string'],
'root_cause' => ['required', 'string'],
'preventive_measures' => ['required', 'string'],
'lessons_learned' => ['nullable', 'string']
```

#### addAction(Request $request, $id)
```php
// 요청 파라미터
'action' => ['required', 'string'],
'action_type' => ['required', 'in:investigation,mitigation,containment,recovery,other']
```

## 💾 데이터베이스 테이블

### auth_security_incidents
보안 사고 메인 테이블

| 컬럼명 | 타입 | 설명 |
|--------|------|------|
| id | BIGINT | Primary Key |
| title | VARCHAR | 사고 제목 |
| type | ENUM | breach/attack/vulnerability/suspicious/other |
| severity | ENUM | low/medium/high/critical |
| status | ENUM | open/investigating/contained/resolved/closed |
| description | TEXT | 사고 설명 |
| affected_systems | JSON | 영향받은 시스템 |
| resolution | TEXT | 해결 내용 |
| root_cause | TEXT | 근본 원인 |
| preventive_measures | TEXT | 예방 조치 |
| lessons_learned | TEXT | 교훈 |
| reported_by | BIGINT | 보고자 ID |
| resolved_by | BIGINT | 해결자 ID |
| resolved_at | TIMESTAMP | 해결 시간 |

### auth_incident_affected_users
사고 영향받은 사용자

| 컬럼명 | 타입 | 설명 |
|--------|------|------|
| id | BIGINT | Primary Key |
| incident_id | BIGINT | 사고 ID |
| user_id | BIGINT | 사용자 ID |

### auth_incident_actions
사고 조치 내역

| 컬럼명 | 타입 | 설명 |
|--------|------|------|
| id | BIGINT | Primary Key |
| incident_id | BIGINT | 사고 ID |
| action | TEXT | 조치 내용 |
| action_type | ENUM | 조치 유형 |
| performed_by | BIGINT | 수행자 ID |

### auth_incident_timeline
사고 타임라인

| 컬럼명 | 타입 | 설명 |
|--------|------|------|
| id | BIGINT | Primary Key |
| incident_id | BIGINT | 사고 ID |
| event | VARCHAR | 이벤트 |
| description | TEXT | 설명 |
| performed_by | BIGINT | 수행자 ID |
| occurred_at | TIMESTAMP | 발생 시간 |

## 📦 Request/Response 예시

### 사고 등록
```json
POST /admin/auth/security-incident
{
    "title": "의심스러운 로그인 시도 감지",
    "type": "attack",
    "severity": "high",
    "description": "특정 IP에서 다수 계정에 대한 무차별 대입 공격 시도가 감지되었습니다.",
    "affected_users": [100, 101, 102],
    "affected_systems": ["authentication", "session"],
    "immediate_action": "해당 IP 차단 및 영향받은 계정 비밀번호 재설정 요구"
}
```

### 사고 해결
```json
POST /admin/auth/security-incident/1/resolve
{
    "resolution": "공격 IP를 영구 차단하고, 영향받은 모든 계정의 비밀번호를 재설정했습니다.",
    "root_cause": "로그인 시도 제한이 적절히 설정되지 않아 무차별 대입 공격이 가능했습니다.",
    "preventive_measures": "1. 로그인 시도 제한을 5회로 강화\n2. IP별 요청 제한 구현\n3. CAPTCHA 자동 활성화 로직 추가",
    "lessons_learned": "보안 설정 검토 주기를 월 1회로 단축 필요"
}
```

## 🔒 보안 고려사항

### 1. 자동 대응 매트릭스

| 심각도 | 자동 대응 조치 |
|--------|---------------|
| Critical | - 모든 세션 종료<br>- 로그인 차단<br>- 2FA 강제 활성화<br>- 관리자 즉시 알림 |
| High | - 영향받은 사용자 세션 종료<br>- 비밀번호 재설정 요구<br>- CAPTCHA 활성화 |
| Medium | - 활동 모니터링 강화<br>- 로그 레벨 상승 |
| Low | - 로그 기록 |

### 2. 사고 유형별 대응

#### 데이터 유출 (Breach)
- 모든 세션 즉시 종료
- 전체 비밀번호 재설정
- 2FA 강제 활성화

#### 공격 (Attack)
- IP 차단 목록 업데이트
- 로그인 제한 강화
- CAPTCHA 활성화

#### 취약점 (Vulnerability)
- 취약점 패치 적용
- 영향 범위 평가
- 임시 보안 조치

#### 의심 활동 (Suspicious)
- 모니터링 강화
- 상세 로그 수집
- 패턴 분석

## 🎨 뷰 파일

### 사고 목록 페이지
**위치**: `/jiny/auth/resources/views/admin/security-incident/index.blade.php`

주요 요소:
- 사고 목록 테이블 (상태, 심각도, 유형별 필터)
- 사고 통계 대시보드
- 빠른 등록 버튼

### 사고 상세 페이지
**위치**: `/jiny/auth/resources/views/admin/security-incident/show.blade.php`

주요 요소:
- 사고 정보 카드
- 타임라인 뷰
- 영향받은 사용자 목록
- 조치 내역
- 상태 업데이트 폼

## ⚙️ 설정

### 자동 대응 활성화
```php
// config/auth-security.php
'auto_response' => [
    'enabled' => true,
    'severity_threshold' => 'high', // high 이상일 때만 자동 대응
]
```

### 알림 설정
```php
'notifications' => [
    'admin_emails' => ['security@example.com'],
    'sms_enabled' => true,
    'slack_webhook' => env('SLACK_SECURITY_WEBHOOK')
]
```

## 📝 활용 예시

### 시나리오 1: DDoS 공격 대응
```javascript
// 1. 사고 등록
const incident = await fetch('/admin/auth/security-incident', {
    method: 'POST',
    body: JSON.stringify({
        title: 'DDoS 공격 감지',
        type: 'attack',
        severity: 'critical',
        description: 'API 엔드포인트에 대한 대규모 DDoS 공격',
        affected_systems: ['api', 'database'],
        immediate_action: 'Rate limiting 강화 및 CDN 보호 모드 활성화'
    })
});

// 2. 조치 추가
await fetch(`/admin/auth/security-incident/${incident.id}/action`, {
    method: 'POST',
    body: JSON.stringify({
        action: 'Cloudflare DDoS 보호 모드 활성화',
        action_type: 'mitigation'
    })
});
```

### 시나리오 2: 데이터 유출 사고
```javascript
// 1. 사고 등록 (자동 대응 트리거)
const incident = await fetch('/admin/auth/security-incident', {
    method: 'POST',
    body: JSON.stringify({
        title: '사용자 데이터 무단 접근 시도',
        type: 'breach',
        severity: 'critical',
        description: 'SQL Injection을 통한 사용자 데이터 접근 시도 발견',
        affected_users: affectedUserIds
    })
});

// 2. 조사 결과 업데이트
await fetch(`/admin/auth/security-incident/${incident.id}`, {
    method: 'PUT',
    body: JSON.stringify({
        status: 'investigating',
        severity: 'critical',
        update_note: 'SQL Injection 취약점 확인, 패치 진행 중'
    })
});

// 3. 사고 해결
await fetch(`/admin/auth/security-incident/${incident.id}/resolve`, {
    method: 'POST',
    body: JSON.stringify({
        resolution: '취약점 패치 완료 및 WAF 규칙 업데이트',
        root_cause: 'User Input Validation 미흡',
        preventive_measures: '1. 전체 SQL 쿼리 파라미터 바인딩 검토\n2. WAF 규칙 강화',
        lessons_learned: '정기적인 보안 감사 필요'
    })
});
```

## 🚨 주의사항

1. **자동 대응 주의**
   - Critical 레벨은 시스템 전체에 영향
   - 잘못된 심각도 설정 시 서비스 중단 가능

2. **개인정보 보호**
   - 사고 설명에 민감한 정보 포함 금지
   - 영향받은 사용자 정보는 ID만 저장

3. **보고서 관리**
   - 자동 생성된 보고서는 암호화 저장
   - 정기적인 보고서 백업 필요

4. **타임라인 정확성**
   - 모든 조치는 즉시 기록
   - 시간 순서 유지 중요

## 🔄 통합 가능 시스템

1. **SIEM (Security Information and Event Management)**
   - Splunk, ELK Stack 연동
   - 실시간 로그 분석

2. **티켓팅 시스템**
   - Jira, ServiceNow 연동
   - 사고 처리 워크플로우

3. **알림 시스템**
   - Slack, Teams 웹훅
   - PagerDuty 연동

4. **위협 인텔리전스**
   - IP 평판 조회
   - 알려진 공격 패턴 매칭