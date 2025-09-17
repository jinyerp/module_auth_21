# 로그 및 통계 (Logs and Statistics)

## 📋 개요
사용자 활동을 추적하고 분석하여 시스템 운영에 필요한 인사이트를 제공하는 로그 관리 및 통계 분석 시스템입니다.

## 🎯 주요 기능

### 1. 로그 관리
- 로그인 히스토리 추적 및 분석
- 계정 활동 로그 기록
- 보안 로그 및 권한 변경 추적
- 로그 데이터 내보내기 (CSV/JSON)

### 2. 사용자 통계
- 가입 통계 (일별/월별/연도별)
- 활성 사용자 분석 (DAU/WAU/MAU)
- 로그인 패턴 분석
- 사용자 유지율 및 코호트 분석

### 3. 실시간 모니터링
- 현재 활성 세션 모니터링
- 실시간 로그인 추적
- 이상 패턴 감지

## 🔗 라우트 (Routes)

### 로그 내보내기
| HTTP 메소드 | URI | 컨트롤러 메소드 | 설명 |
|------------|-----|----------------|------|
| GET | `/admin/auth/export/login-history` | AdminLogExportController::exportLoginHistory | 로그인 로그 내보내기 |
| GET | `/admin/auth/export/account-logs` | AdminLogExportController::exportAccountLogs | 활동 로그 내보내기 |
| GET | `/admin/auth/export/security-logs` | AdminLogExportController::exportSecurityLogs | 보안 로그 내보내기 |
| GET | `/admin/auth/export/permission-logs` | AdminLogExportController::permissionLogs | 권한 변경 로그 |

### 통계 분석
| HTTP 메소드 | URI | 컨트롤러 메소드 | 설명 |
|------------|-----|----------------|------|
| GET | `/admin/auth/statistics/registrations` | AdminStatisticsController::registrations | 가입 통계 |
| GET | `/admin/auth/statistics/active-users` | AdminStatisticsController::activeUsers | 활성 사용자 통계 |
| GET | `/admin/auth/statistics/login-patterns` | AdminStatisticsController::loginPatterns | 로그인 패턴 분석 |
| GET | `/admin/auth/statistics/retention` | AdminStatisticsController::retention | 사용자 유지율 |

## 🎮 컨트롤러

### AdminLogExportController
**위치**: `/jiny/auth/App/Http/Controllers/Admin/AdminLogExportController.php`

#### exportLoginHistory(Request $request)
```php
// 요청 파라미터
'date_from' => ['nullable', 'date'],
'date_to' => ['nullable', 'date'],
'status' => ['nullable', 'in:success,failed,suspicious'],
'format' => ['required', 'in:csv,json']
```

#### exportAccountLogs(Request $request)
```php
// 요청 파라미터
'date_from' => ['nullable', 'date'],
'date_to' => ['nullable', 'date'],
'event' => ['nullable', 'string'],
'format' => ['required', 'in:csv,json']
```

### AdminStatisticsController
**위치**: `/jiny/auth/App/Http/Controllers/Admin/AdminStatisticsController.php`

#### registrations(Request $request)
```php
// 요청 파라미터
'period' => ['nullable', 'in:day,week,month,year'],
'start_date' => ['nullable', 'date'],
'end_date' => ['nullable', 'date']
```

#### retention(Request $request)
```php
// 요청 파라미터
'cohort_period' => ['nullable', 'in:week,month'],
'periods' => ['nullable', 'integer', 'min:1', 'max:12']
```

## 💾 데이터베이스 테이블

### auth_login_histories
로그인 시도 기록

| 컬럼명 | 타입 | 설명 |
|--------|------|------|
| id | BIGINT | Primary Key |
| user_id | BIGINT | 사용자 ID |
| ip_address | VARCHAR | IP 주소 |
| user_agent | TEXT | User Agent |
| status | ENUM | success/failed/suspicious |
| failure_reason | VARCHAR | 실패 사유 |
| created_at | TIMESTAMP | 로그인 시도 시간 |

### auth_account_logs
계정 활동 로그

| 컬럼명 | 타입 | 설명 |
|--------|------|------|
| id | BIGINT | Primary Key |
| user_id | BIGINT | 사용자 ID |
| event | VARCHAR | 이벤트 유형 |
| description | TEXT | 설명 |
| performed_by | BIGINT | 수행자 ID |
| ip_address | VARCHAR | IP 주소 |
| created_at | TIMESTAMP | 발생 시간 |

### auth_password_errors
비밀번호 오류 로그

| 컬럼명 | 타입 | 설명 |
|--------|------|------|
| id | BIGINT | Primary Key |
| user_id | BIGINT | 사용자 ID |
| email | VARCHAR | 이메일 |
| error_count | INT | 오류 횟수 |
| locked_until | TIMESTAMP | 잠금 종료 시간 |
| ip_address | VARCHAR | IP 주소 |

## 📊 통계 메트릭

### 가입 통계
```json
{
    "overview": {
        "total_users": 15234,
        "today": 45,
        "this_week": 312,
        "this_month": 1250
    },
    "trends": {
        "daily": [
            {"date": "2025-01-17", "count": 45},
            {"date": "2025-01-16", "count": 52}
        ],
        "monthly": [
            {"month": "2025-01", "count": 1250},
            {"month": "2024-12", "count": 1180}
        ]
    },
    "by_source": {
        "organic": 8500,
        "social": 3200,
        "referral": 2100,
        "direct": 1434
    }
}
```

### 활성 사용자 통계
```json
{
    "metrics": {
        "dau": 3250,  // Daily Active Users
        "wau": 8900,  // Weekly Active Users
        "mau": 12500  // Monthly Active Users
    },
    "stickiness": {
        "dau_mau": 0.26,  // DAU/MAU ratio
        "wau_mau": 0.71   // WAU/MAU ratio
    },
    "activity_levels": {
        "power_users": 850,    // 매일 접속
        "regular_users": 3200, // 주 3-6회
        "casual_users": 4850,  // 주 1-2회
        "dormant_users": 3600  // 30일 이상 미접속
    }
}
```

### 로그인 패턴
```json
{
    "by_hour": {
        "peak_hours": ["09:00", "14:00", "20:00"],
        "distribution": [/* 24시간 분포 데이터 */]
    },
    "by_day": {
        "peak_days": ["Monday", "Wednesday"],
        "weekend_ratio": 0.35
    },
    "auth_methods": {
        "password": 7500,
        "social": 3200,
        "2fa": 1800
    },
    "devices": {
        "desktop": 6500,
        "mobile": 4800,
        "tablet": 1200
    }
}
```

### 코호트 분석
```json
{
    "cohorts": [
        {
            "period": "2024-12",
            "users": 1180,
            "retention": {
                "day_1": 0.85,
                "day_7": 0.65,
                "day_30": 0.45,
                "day_90": 0.35
            }
        }
    ],
    "churn_rate": {
        "monthly": 0.12,
        "quarterly": 0.28
    },
    "lifetime_value": {
        "average_days": 245,
        "median_days": 180
    }
}
```

## 📦 내보내기 형식

### CSV 내보내기
```csv
user_id,user_name,user_email,ip_address,status,created_at
1,홍길동,hong@example.com,192.168.1.1,success,2025-01-17 10:00:00
2,김철수,kim@example.com,192.168.1.2,failed,2025-01-17 10:05:00
```

### JSON 내보내기
```json
[
    {
        "user_id": 1,
        "user_name": "홍길동",
        "user_email": "hong@example.com",
        "ip_address": "192.168.1.1",
        "status": "success",
        "created_at": "2025-01-17 10:00:00"
    }
]
```

## 🎨 뷰 파일

### 가입 통계 대시보드
**위치**: `/jiny/auth/resources/views/admin/statistics/registrations.blade.php`

주요 차트:
- 시계열 라인 차트 (일별/월별 가입 추이)
- 파이 차트 (가입 경로별 분포)
- 바 차트 (시간대별 가입 패턴)

### 활성 사용자 대시보드
**위치**: `/jiny/auth/resources/views/admin/statistics/active-users.blade.php`

주요 위젯:
- 실시간 활성 사용자 카운터
- DAU/WAU/MAU 게이지
- 활동 레벨별 사용자 분포

### 코호트 분석 테이블
**위치**: `/jiny/auth/resources/views/admin/statistics/retention.blade.php`

주요 요소:
- 코호트 히트맵
- 유지율 추이 그래프
- 이탈률 분석

## ⚙️ 설정

### 로그 보관 기간
```php
// config/auth-logs.php
'retention' => [
    'login_history' => 90,    // 90일
    'account_logs' => 365,    // 1년
    'security_logs' => 730,   // 2년
]
```

### 통계 계산 주기
```php
'statistics' => [
    'cache_ttl' => 3600,      // 1시간 캐싱
    'realtime_threshold' => 5  // 5분 이내는 실시간
]
```

## 📝 활용 예시

### 시나리오 1: 월간 보고서 생성
```javascript
// 지난달 데이터 수집
const lastMonth = new Date();
lastMonth.setMonth(lastMonth.getMonth() - 1);

// 가입 통계 조회
const registrations = await fetch('/admin/auth/statistics/registrations?' + 
    `start_date=${lastMonth.toISOString()}&period=day`);

// 로그인 로그 내보내기
const loginLogs = await fetch('/admin/auth/export/login-history?' +
    `date_from=${lastMonth.toISOString()}&format=csv`);

// 보고서 생성
generateMonthlyReport({
    registrations: await registrations.json(),
    loginLogs: await loginLogs.blob()
});
```

### 시나리오 2: 이상 패턴 감지
```javascript
// 실시간 모니터링
const monitor = setInterval(async () => {
    const activeUsers = await fetch('/admin/auth/statistics/active-users');
    const data = await activeUsers.json();
    
    // 이상 감지
    if (data.metrics.dau > normalRange.max) {
        alert('비정상적으로 높은 활동 감지');
    }
    
    if (data.activity_levels.dormant_users > threshold) {
        notifyAdmin('휴면 사용자 증가 알림');
    }
}, 60000); // 1분마다 체크
```

## 🚨 주의사항

1. **개인정보 보호**
   - 로그 내보내기 시 민감 정보 필터링
   - GDPR/개인정보보호법 준수

2. **성능 고려**
   - 대용량 로그는 백그라운드 처리
   - 통계는 캐싱하여 부하 감소

3. **저장 공간**
   - 로그 자동 정리 스케줄 설정
   - 오래된 로그는 아카이빙

4. **데이터 정확성**
   - 시간대(timezone) 일관성 유지
   - 중복 로그 방지 로직 필요

## 🔄 자동화 및 스케줄링

### 일일 통계 리포트
```php
// app/Console/Kernel.php
$schedule->call(function () {
    app(AdminStatisticsController::class)->generateDailyReport();
})->daily()->at('01:00');
```

### 로그 정리
```php
$schedule->command('auth:logs:cleanup')
    ->weekly()
    ->sundays()
    ->at('03:00');
```

### 이상 패턴 알림
```php
$schedule->call(function () {
    app(AnomalyDetectionService::class)->checkPatterns();
})->everyFiveMinutes();
```

## 📈 대시보드 위젯

관리자 대시보드에 표시할 수 있는 위젯:

1. **오늘의 지표**
   - 신규 가입자
   - 활성 사용자
   - 로그인 성공률

2. **주간 트렌드**
   - 7일 이동 평균
   - 주간 성장률
   - 피크 시간대

3. **알림 센터**
   - 보안 경고
   - 이상 패턴
   - 시스템 상태

4. **빠른 작업**
   - 로그 내보내기
   - 리포트 생성
   - 통계 새로고침