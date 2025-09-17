# 6단계: 이메일 및 SMS 관리

## 📋 개요
이메일 및 SMS 발송 시스템을 통합 관리하여 사용자 알림, 마케팅, 인증 메시지를 효율적으로 처리하고 모니터링합니다.

## 🎯 주요 기능

### 6.1 이메일 템플릿 관리
- 템플릿 생성/수정/삭제
- 변수 바인딩 시스템
- 미리보기 기능
- 버전 관리

### 6.2 이메일 발송 관리
- 대량 이메일 발송
- 예약 발송
- 발송 로그 추적
- 반송 메일 처리

### 6.3 SMS 발송 시스템
- SMS 공급자 연동 (Twilio, Aligo 등)
- 템플릿 메시지
- 발신번호 관리
- 발송 제한 설정

### 6.4 알림 설정
- 사용자별 알림 설정
- 알림 채널 선택 (이메일/SMS/푸시)
- 알림 빈도 제어
- 수신거부 관리

## 🔗 라우트 (Routes)

### 이메일 템플릿 관리
| HTTP 메소드 | URI | 컨트롤러 메소드 | 설명 |
|------------|-----|----------------|------|
| GET | `/admin/auth/email/templates` | AdminEmailTemplateController::index | 템플릿 목록 |
| GET | `/admin/auth/email/templates/create` | AdminEmailTemplateController::create | 템플릿 생성 폼 |
| POST | `/admin/auth/email/templates` | AdminEmailTemplateController::store | 템플릿 저장 |
| GET | `/admin/auth/email/templates/{id}/edit` | AdminEmailTemplateController::edit | 템플릿 수정 폼 |
| PUT | `/admin/auth/email/templates/{id}` | AdminEmailTemplateController::update | 템플릿 업데이트 |
| DELETE | `/admin/auth/email/templates/{id}` | AdminEmailTemplateController::destroy | 템플릿 삭제 |
| GET | `/admin/auth/email/templates/{id}/preview` | AdminEmailTemplateController::preview | 템플릿 미리보기 |

### 이메일 발송 관리
| HTTP 메소드 | URI | 컨트롤러 메소드 | 설명 |
|------------|-----|----------------|------|
| GET | `/admin/auth/email/logs` | AdminEmailLogController::index | 발송 로그 |
| GET | `/admin/auth/email/compose` | AdminEmailController::compose | 이메일 작성 |
| POST | `/admin/auth/email/send` | AdminEmailController::send | 즉시 발송 |
| POST | `/admin/auth/email/schedule` | AdminEmailController::schedule | 예약 발송 |
| GET | `/admin/auth/email/queue` | AdminEmailController::queue | 발송 대기열 |
| POST | `/admin/auth/email/bulk` | AdminEmailController::bulk | 대량 발송 |
| GET | `/admin/auth/email/bounced` | AdminEmailController::bounced | 반송 메일 |

### SMS 관리
| HTTP 메소드 | URI | 컨트롤러 메소드 | 설명 |
|------------|-----|----------------|------|
| GET | `/admin/auth/sms/providers` | AdminSmsProviderController::index | SMS 공급자 목록 |
| POST | `/admin/auth/sms/providers` | AdminSmsProviderController::store | 공급자 추가 |
| PUT | `/admin/auth/sms/providers/{id}` | AdminSmsProviderController::update | 공급자 설정 수정 |
| POST | `/admin/auth/sms/providers/{id}/test` | AdminSmsProviderController::test | 테스트 발송 |
| GET | `/admin/auth/sms/send` | AdminSmsController::compose | SMS 작성 |
| POST | `/admin/auth/sms/send` | AdminSmsController::send | SMS 발송 |
| GET | `/admin/auth/sms/logs` | AdminSmsController::logs | 발송 로그 |
| GET | `/admin/auth/sms/templates` | AdminSmsTemplateController::index | SMS 템플릿 |

### 알림 설정
| HTTP 메소드 | URI | 컨트롤러 메소드 | 설명 |
|------------|-----|----------------|------|
| GET | `/user/notifications/preferences` | UserNotificationController::preferences | 알림 설정 |
| POST | `/user/notifications/preferences` | UserNotificationController::updatePreferences | 설정 업데이트 |
| POST | `/user/notifications/unsubscribe/{token}` | UserNotificationController::unsubscribe | 수신거부 |
| GET | `/admin/auth/notifications/settings` | AdminNotificationController::settings | 전역 알림 설정 |
| POST | `/admin/auth/notifications/test` | AdminNotificationController::test | 테스트 알림 |

## 🎮 컨트롤러

### AdminEmailTemplateController
**위치**: `/jiny/auth/App/Http/Controllers/Admin/AdminEmailTemplateController.php`

#### store(Request $request)
```php
// 요청 파라미터
'name' => ['required', 'string', 'max:255'],
'subject' => ['required', 'string'],
'content' => ['required', 'string'],
'type' => ['required', 'in:system,marketing,transactional'],
'variables' => ['nullable', 'array']

// 처리
1. 템플릿 생성
2. 변수 파싱
3. 버전 저장
4. 캐시 업데이트
```

### AdminSmsController
**위치**: `/jiny/auth/App/Http/Controllers/Admin/AdminSmsController.php`

#### send(Request $request)
```php
// 요청 파라미터
'recipients' => ['required', 'array'],
'message' => ['required', 'string', 'max:90'],
'provider' => ['nullable', 'exists:sms_providers,id'],
'schedule_at' => ['nullable', 'date', 'after:now']

// 처리
1. 발신번호 확인
2. 수신자 검증
3. 메시지 길이 체크
4. 공급자 API 호출
5. 발송 로그 기록
```

## 💾 데이터베이스 테이블

### email_templates
| 컬럼명 | 타입 | 설명 |
|--------|------|------|
| id | BIGINT | Primary Key |
| name | VARCHAR | 템플릿명 |
| slug | VARCHAR | 슬러그 (Unique) |
| subject | VARCHAR | 제목 |
| content | TEXT | 내용 (HTML) |
| plain_text | TEXT | 텍스트 버전 |
| type | ENUM | system/marketing/transactional |
| variables | JSON | 사용 가능 변수 |
| version | INT | 버전 번호 |
| is_active | BOOLEAN | 활성화 여부 |

### email_logs
| 컬럼명 | 타입 | 설명 |
|--------|------|------|
| id | BIGINT | Primary Key |
| user_id | BIGINT | 수신자 ID |
| template_id | BIGINT | 템플릿 ID |
| to | VARCHAR | 수신 이메일 |
| cc | TEXT | 참조 |
| bcc | TEXT | 숨은참조 |
| subject | VARCHAR | 제목 |
| content | TEXT | 내용 |
| status | ENUM | pending/sent/failed/bounced |
| sent_at | TIMESTAMP | 발송 시간 |
| opened_at | TIMESTAMP | 열람 시간 |
| clicked_at | TIMESTAMP | 클릭 시간 |

### sms_providers
| 컬럼명 | 타입 | 설명 |
|--------|------|------|
| id | BIGINT | Primary Key |
| name | VARCHAR | 공급자명 |
| driver | VARCHAR | 드라이버 (twilio/aligo/etc) |
| config | JSON | API 설정 (암호화) |
| sender_number | VARCHAR | 발신번호 |
| daily_limit | INT | 일일 발송 제한 |
| is_active | BOOLEAN | 활성화 여부 |
| is_default | BOOLEAN | 기본 공급자 |

### sms_logs
| 컬럼명 | 타입 | 설명 |
|--------|------|------|
| id | BIGINT | Primary Key |
| user_id | BIGINT | 수신자 ID |
| provider_id | BIGINT | 공급자 ID |
| to | VARCHAR | 수신 번호 |
| from | VARCHAR | 발신 번호 |
| message | TEXT | 메시지 |
| status | ENUM | pending/sent/failed/delivered |
| cost | DECIMAL | 발송 비용 |
| sent_at | TIMESTAMP | 발송 시간 |
| delivered_at | TIMESTAMP | 전달 시간 |
| error | TEXT | 에러 메시지 |

### notification_preferences
| 컬럼명 | 타입 | 설명 |
|--------|------|------|
| id | BIGINT | Primary Key |
| user_id | BIGINT | 사용자 ID |
| channel | ENUM | email/sms/push |
| type | VARCHAR | 알림 유형 |
| enabled | BOOLEAN | 활성화 여부 |
| frequency | ENUM | realtime/daily/weekly |
| quiet_hours | JSON | 방해금지 시간 |

## 📧 이메일 템플릿 시스템

### 템플릿 변수 시스템
```php
// 템플릿 내용
$template = "안녕하세요 {{ user.name }}님,\n
{{ product.name }} 구매를 환영합니다.\n
가격: {{ product.price | currency }}\n
만료일: {{ expire_date | date:'Y-m-d' }}";

// 변수 바인딩
$variables = [
    'user' => ['name' => '홍길동'],
    'product' => ['name' => '프리미엄 플랜', 'price' => 50000],
    'expire_date' => '2025-12-31'
];

// 렌더링
$rendered = $this->renderTemplate($template, $variables);
```

### 이메일 큐 처리
```php
// app/Jobs/SendBulkEmail.php
class SendBulkEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public function handle()
    {
        $recipients = User::where('subscribe_email', true)->get();
        
        foreach ($recipients->chunk(100) as $chunk) {
            foreach ($chunk as $user) {
                Mail::to($user)
                    ->queue(new MarketingEmail($this->template, $user));
            }
            
            // Rate limiting
            sleep(1);
        }
    }
}
```

## 📱 SMS 발송 시스템

### Twilio 연동
```php
// app/Services/SmsProviders/TwilioProvider.php
use Twilio\Rest\Client;

class TwilioProvider implements SmsProviderInterface
{
    protected $client;
    
    public function __construct($config)
    {
        $this->client = new Client(
            $config['account_sid'],
            $config['auth_token']
        );
    }
    
    public function send($to, $message, $from = null)
    {
        try {
            $result = $this->client->messages->create(
                $to,
                [
                    'from' => $from ?? $this->defaultFrom,
                    'body' => $message
                ]
            );
            
            return [
                'success' => true,
                'message_id' => $result->sid,
                'cost' => $result->price
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
```

### 한국 SMS 서비스 (알리고)
```php
// app/Services/SmsProviders/AligoProvider.php
class AligoProvider implements SmsProviderInterface
{
    public function send($to, $message, $from = null)
    {
        $data = [
            'key' => $this->config['api_key'],
            'user_id' => $this->config['user_id'],
            'sender' => $from ?? $this->config['sender'],
            'receiver' => $to,
            'msg' => $message,
            'testmode_yn' => $this->config['test_mode'] ? 'Y' : 'N'
        ];
        
        $response = Http::asForm()->post(
            'https://apis.aligo.in/send/',
            $data
        );
        
        $result = $response->json();
        
        return [
            'success' => $result['result_code'] == '1',
            'message_id' => $result['msg_id'] ?? null,
            'error' => $result['message'] ?? null
        ];
    }
}
```

## 📦 Request/Response 예시

### 이메일 템플릿 생성
```http
POST /admin/auth/email/templates
Content-Type: application/json

{
    "name": "회원가입 환영",
    "slug": "welcome_email",
    "subject": "{{ app.name }}에 오신 것을 환영합니다!",
    "content": "<h1>환영합니다 {{ user.name }}님!</h1>...",
    "type": "system",
    "variables": ["user.name", "user.email", "app.name"]
}
```

### SMS 대량 발송
```http
POST /admin/auth/sms/bulk
Content-Type: application/json

{
    "recipients": [
        {"phone": "010-1234-5678", "name": "홍길동"},
        {"phone": "010-2345-6789", "name": "김철수"}
    ],
    "template": "sms_promotion",
    "variables": {
        "discount": "20%",
        "expire_date": "2025-01-31"
    },
    "provider_id": 1
}
```

### 알림 설정 업데이트
```http
POST /user/notifications/preferences
Content-Type: application/json

{
    "email": {
        "marketing": true,
        "system": true,
        "frequency": "weekly"
    },
    "sms": {
        "marketing": false,
        "system": true,
        "frequency": "realtime"
    },
    "quiet_hours": {
        "enabled": true,
        "start": "22:00",
        "end": "08:00"
    }
}
```

## 🔔 알림 시스템

### 알림 채널 관리
```php
// app/Notifications/UserNotification.php
class UserNotification extends Notification
{
    public function via($notifiable)
    {
        $channels = [];
        
        // 사용자 설정 확인
        $preferences = $notifiable->notificationPreferences;
        
        if ($preferences->email_enabled) {
            $channels[] = 'mail';
        }
        
        if ($preferences->sms_enabled) {
            $channels[] = 'sms';
        }
        
        if ($preferences->push_enabled) {
            $channels[] = 'database';
        }
        
        return $channels;
    }
    
    public function toMail($notifiable)
    {
        $template = EmailTemplate::where('slug', $this->templateSlug)->first();
        
        return (new MailMessage)
            ->subject($this->renderTemplate($template->subject))
            ->view('emails.template', [
                'content' => $this->renderTemplate($template->content)
            ]);
    }
    
    public function toSms($notifiable)
    {
        $template = SmsTemplate::where('slug', $this->templateSlug)->first();
        
        return [
            'to' => $notifiable->phone,
            'message' => $this->renderTemplate($template->content)
        ];
    }
}
```

### 방해금지 시간 체크
```php
public function shouldSendNow($user, $channel)
{
    $preferences = $user->notificationPreferences;
    
    if (!$preferences->quiet_hours['enabled']) {
        return true;
    }
    
    $now = Carbon::now($user->timezone);
    $start = Carbon::parse($preferences->quiet_hours['start'], $user->timezone);
    $end = Carbon::parse($preferences->quiet_hours['end'], $user->timezone);
    
    // 방해금지 시간 체크
    if ($end < $start) {
        // 자정을 넘는 경우
        return !($now >= $start || $now <= $end);
    } else {
        return !$now->between($start, $end);
    }
}
```

## 📊 발송 통계

### 이메일 통계
```php
public function getEmailStatistics($dateFrom, $dateTo)
{
    return [
        'sent' => EmailLog::whereBetween('sent_at', [$dateFrom, $dateTo])
            ->count(),
        'opened' => EmailLog::whereBetween('sent_at', [$dateFrom, $dateTo])
            ->whereNotNull('opened_at')
            ->count(),
        'clicked' => EmailLog::whereBetween('sent_at', [$dateFrom, $dateTo])
            ->whereNotNull('clicked_at')
            ->count(),
        'bounced' => EmailLog::whereBetween('sent_at', [$dateFrom, $dateTo])
            ->where('status', 'bounced')
            ->count(),
        'open_rate' => $this->calculateOpenRate($dateFrom, $dateTo),
        'click_rate' => $this->calculateClickRate($dateFrom, $dateTo)
    ];
}
```

### SMS 발송 비용 계산
```php
public function calculateSmsCost($month)
{
    $logs = SmsLog::whereMonth('sent_at', $month)->get();
    
    $costs = [
        'total' => $logs->sum('cost'),
        'by_provider' => $logs->groupBy('provider_id')
            ->map(function ($group) {
                return $group->sum('cost');
            }),
        'by_type' => [
            'marketing' => $logs->where('type', 'marketing')->sum('cost'),
            'transactional' => $logs->where('type', 'transactional')->sum('cost'),
            'otp' => $logs->where('type', 'otp')->sum('cost')
        ]
    ];
    
    return $costs;
}
```

## 🚨 주의사항

1. **이메일 발송**
   - SPF/DKIM 설정 필수
   - 반송률 모니터링
   - IP 평판 관리

2. **SMS 발송**
   - 발신번호 사전등록
   - 광고성 메시지 규정 준수
   - 수신거부 처리

3. **발송 제한**
   - Rate limiting 적용
   - 일일 발송량 제한
   - 스팸 필터링

4. **개인정보 보호**
   - 수신동의 관리
   - 개인정보 암호화
   - 로그 보관 기간

## 📝 활용 예시

### 시나리오 1: 회원가입 환영 이메일
```php
// 회원가입 후 자동 발송
event(new UserRegistered($user));

// EventListener
public function handle(UserRegistered $event)
{
    $template = EmailTemplate::where('slug', 'welcome_email')->first();
    
    Mail::to($event->user)->send(
        new TemplatedEmail($template, [
            'user' => $event->user,
            'verification_url' => $this->generateVerificationUrl($event->user)
        ])
    );
}
```

### 시나리오 2: OTP SMS 발송
```php
public function sendOtp($phone)
{
    $otp = rand(100000, 999999);
    
    // OTP 저장
    Cache::put("otp_{$phone}", $otp, 300); // 5분
    
    // SMS 발송
    $message = "[인증번호] {$otp} - 5분 이내에 입력해주세요.";
    
    app(SmsService::class)->send($phone, $message, 'otp');
    
    return ['success' => true];
}
```

### 시나리오 3: 마케팅 이메일 캠페인
```php
// 타겟 사용자 선정
$targets = User::where('subscribe_marketing', true)
    ->where('last_purchase', '>', now()->subDays(30))
    ->get();

// 캠페인 생성
$campaign = EmailCampaign::create([
    'name' => '월간 프로모션',
    'template_id' => $template->id,
    'scheduled_at' => now()->addDays(1),
    'target_count' => $targets->count()
]);

// 발송 예약
SendCampaignEmail::dispatch($campaign, $targets)
    ->delay(now()->addDays(1));
```