# 대량 작업 (Bulk Operations)

## 📋 개요
관리자가 여러 사용자를 선택하여 한번에 작업을 수행할 수 있는 대량 처리 기능입니다.

## 🎯 주요 기능

### 1. 일괄 활성화 (Bulk Activate)
휴면/비활성 계정을 한번에 활성화합니다.

### 2. 일괄 비활성화 (Bulk Deactivate) 
정책 위반 등의 사유로 여러 계정을 동시에 차단합니다.

### 3. 일괄 삭제 (Bulk Delete)
선택된 계정들을 소프트/하드 삭제하며, 백업 옵션을 제공합니다.

### 4. 일괄 내보내기 (Bulk Export)
사용자 데이터를 CSV, Excel, JSON 형식으로 내보냅니다.

### 5. 일괄 가져오기 (Bulk Import)
CSV/Excel 파일로 여러 사용자를 한번에 등록합니다.

### 6. 일괄 이메일 발송
선택된 사용자들에게 공지사항이나 안내 이메일을 발송합니다.

### 7. 일괄 비밀번호 재설정
보안 이슈 발생 시 다수 사용자의 비밀번호를 일괄 재설정합니다.

### 8. 일괄 등급 변경
프로모션이나 정책에 따라 사용자 등급을 일괄 변경합니다.

### 9. 일괄 포인트 지급
이벤트 보상 등으로 다수 사용자에게 포인트를 일괄 지급합니다.

## 🔗 라우트 (Routes)

| HTTP 메소드 | URI | 컨트롤러 메소드 | 설명 |
|------------|-----|----------------|------|
| POST | `/admin/auth/bulk/activate` | AdminBulkController::activate | 일괄 활성화 |
| POST | `/admin/auth/bulk/deactivate` | AdminBulkController::deactivate | 일괄 비활성화 |
| POST | `/admin/auth/bulk/delete` | AdminBulkController::delete | 일괄 삭제 |
| POST | `/admin/auth/bulk/export` | AdminBulkController::export | 일괄 내보내기 |
| POST | `/admin/auth/bulk/import` | AdminBulkController::import | 일괄 가져오기 |
| POST | `/admin/auth/bulk/send-email` | AdminBulkController::sendEmail | 일괄 이메일 발송 |
| POST | `/admin/auth/bulk/reset-password` | AdminBulkController::resetPassword | 일괄 비밀번호 재설정 |
| POST | `/admin/auth/bulk/change-grade` | AdminBulkController::changeGrade | 일괄 등급 변경 |
| POST | `/admin/auth/bulk/add-points` | AdminBulkController::addPoints | 일괄 포인트 지급 |

## 🎮 컨트롤러
**위치**: `/jiny/auth/App/Http/Controllers/Admin/AdminBulkController.php`

### 주요 메소드

#### activate(Request $request)
```php
// 요청 파라미터
'user_ids' => ['required', 'array'],
'send_email' => ['boolean'] // 알림 이메일 발송 여부
```

#### deactivate(Request $request)
```php
// 요청 파라미터
'user_ids' => ['required', 'array'],
'reason' => ['required', 'string'],
'until_date' => ['nullable', 'date'] // 비활성화 종료일
```

#### delete(Request $request)
```php
// 요청 파라미터
'user_ids' => ['required', 'array'],
'delete_type' => ['required', 'in:soft,hard'],
'backup' => ['boolean'], // 백업 여부
'admin_password' => ['required'] // 관리자 비밀번호 확인
```

#### export(Request $request)
```php
// 요청 파라미터
'user_ids' => ['nullable', 'array'], // 빈 경우 전체 내보내기
'format' => ['required', 'in:csv,excel,json'],
'fields' => ['required', 'array'] // 내보낼 필드 선택
```

#### import(Request $request)
```php
// 요청 파라미터
'file' => ['required', 'file', 'mimes:csv,xlsx,xls'],
'update_existing' => ['boolean'] // 기존 사용자 업데이트 여부
```

## 💾 데이터베이스 테이블

### auth_grade_histories
등급 변경 이력을 저장합니다.

| 컬럼명 | 타입 | 설명 |
|--------|------|------|
| id | BIGINT | Primary Key |
| user_id | BIGINT | 사용자 ID |
| old_grade | VARCHAR | 이전 등급 |
| new_grade | VARCHAR | 새 등급 |
| reason | VARCHAR | 변경 사유 |
| changed_by | BIGINT | 변경한 관리자 ID |
| created_at | TIMESTAMP | 생성일시 |

## 📦 Request/Response 예시

### 일괄 활성화 요청
```json
POST /admin/auth/bulk/activate
{
    "user_ids": [1, 2, 3, 4, 5],
    "send_email": true
}
```

### 응답
```json
{
    "success": true,
    "message": "5명의 사용자가 활성화되었습니다.",
    "activated_count": 5
}
```

### 일괄 내보내기 요청
```json
POST /admin/auth/bulk/export
{
    "format": "csv",
    "fields": ["id", "name", "email", "created_at"],
    "user_ids": [1, 2, 3]
}
```

### CSV 응답
```csv
id,name,email,created_at
1,홍길동,hong@example.com,2025-01-01 10:00:00
2,김철수,kim@example.com,2025-01-02 11:00:00
3,이영희,lee@example.com,2025-01-03 12:00:00
```

## 🔒 보안 고려사항

1. **관리자 권한 필수**
   - 모든 대량 작업은 관리자 권한이 필요합니다
   - `admin` 미들웨어로 보호됩니다

2. **2단계 인증**
   - 삭제 작업 시 관리자 비밀번호 재확인
   - 중요한 작업에 대한 추가 보안 레이어

3. **트랜잭션 처리**
   - 모든 대량 작업은 DB 트랜잭션으로 처리
   - 실패 시 전체 롤백으로 데이터 일관성 보장

4. **백업 옵션**
   - 삭제 전 데이터 백업 기능
   - JSON 형식으로 로컬 스토리지에 저장

5. **활동 로그**
   - 모든 대량 작업은 `activity_log`에 기록
   - 누가, 언제, 무엇을 했는지 추적 가능

## 🎨 뷰 파일
대량 작업은 주로 API 방식으로 동작하므로 별도의 뷰 파일이 없습니다.
관리자 패널의 사용자 목록 페이지에서 JavaScript를 통해 호출됩니다.

## ⚙️ 설정
대량 작업 관련 설정은 없으며, 시스템 기본값을 사용합니다.

## 📝 활용 예시

### 시나리오 1: 이벤트 종료 후 참여자 포인트 지급
```javascript
// 이벤트 참여자 ID 배열
const eventParticipants = [10, 20, 30, 40, 50];

// 일괄 포인트 지급
fetch('/admin/auth/bulk/add-points', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken
    },
    body: JSON.stringify({
        user_ids: eventParticipants,
        points: 1000,
        reason: '신년 이벤트 참여 보상',
        expires_at: '2025-12-31'
    })
});
```

### 시나리오 2: 휴면 계정 일괄 처리
```javascript
// 6개월 이상 미접속 사용자 비활성화
fetch('/admin/auth/bulk/deactivate', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken
    },
    body: JSON.stringify({
        user_ids: dormantUserIds,
        reason: '6개월 이상 미접속으로 인한 휴면 처리',
        until_date: null // 무기한
    })
});
```

## 🚨 주의사항

1. **대용량 처리**
   - 1000명 이상 처리 시 타임아웃 주의
   - 필요시 큐 작업으로 백그라운드 처리 권장

2. **이메일 발송**
   - 대량 이메일은 큐를 통해 순차 발송
   - 발송 서버의 제한 사항 확인 필요

3. **파일 크기 제한**
   - 가져오기 파일 최대 10MB
   - php.ini의 upload_max_filesize 설정 확인

4. **권한 체크**
   - 관리자 계정은 삭제/비활성화 불가
   - 본인 계정도 처리 대상에서 제외