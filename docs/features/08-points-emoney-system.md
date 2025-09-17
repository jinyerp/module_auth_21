# 8단계: 포인트 및 eMoney 시스템

## 📋 개요
포인트와 eMoney(전자화폐)를 통한 리워드 시스템과 결제 수단을 제공하여 사용자 참여를 유도하고 충성도를 높입니다.

## 🎯 주요 기능

### 8.1 포인트 시스템
- 포인트 적립/차감
- 포인트 내역 조회
- 포인트 유효기간 관리
- 포인트 정책 설정

### 8.2 eMoney 시스템
- eMoney 충전/환불
- eMoney 송금
- eMoney 결제
- 잔액 관리

### 8.3 리워드 프로그램
- 출석 체크 보상
- 활동 보상
- 이벤트 포인트
- 추천인 보상

### 8.4 정산 및 환전
- 포인트 → eMoney 전환
- eMoney → 현금 환전
- 정산 내역 관리
- 세금 처리

## 🔗 라우트 (Routes)

### 포인트 관리 (사용자)
| HTTP 메소드 | URI | 컨트롤러 메소드 | 설명 |
|------------|-----|----------------|------|
| GET | `/user/points` | UserPointController::index | 포인트 내역 |
| GET | `/user/points/balance` | UserPointController::balance | 포인트 잔액 |
| GET | `/user/points/expiring` | UserPointController::expiring | 만료 예정 포인트 |
| POST | `/user/points/transfer` | UserPointController::transfer | 포인트 양도 |
| GET | `/user/points/history` | UserPointController::history | 상세 내역 |

### 포인트 관리 (관리자)
| HTTP 메소드 | URI | 컨트롤러 메소드 | 설명 |
|------------|-----|----------------|------|
| GET | `/admin/auth/points` | AdminPointController::index | 포인트 관리 |
| POST | `/admin/auth/points/add` | AdminPointController::add | 포인트 지급 |
| POST | `/admin/auth/points/deduct` | AdminPointController::deduct | 포인트 차감 |
| GET | `/admin/auth/points/policies` | AdminPointController::policies | 포인트 정책 |
| PUT | `/admin/auth/points/policies` | AdminPointController::updatePolicies | 정책 업데이트 |
| GET | `/admin/auth/points/statistics` | AdminPointController::statistics | 포인트 통계 |

### eMoney 관리 (사용자)
| HTTP 메소드 | URI | 컨트롤러 메소드 | 설명 |
|------------|-----|----------------|------|
| GET | `/user/emoney` | UserEmoneyController::index | eMoney 대시보드 |
| GET | `/user/emoney/balance` | UserEmoneyController::balance | 잔액 조회 |
| POST | `/user/emoney/charge` | UserEmoneyController::charge | 충전 요청 |
| POST | `/user/emoney/transfer` | UserEmoneyController::transfer | 송금 |
| POST | `/user/emoney/withdraw` | UserEmoneyController::withdraw | 출금 요청 |
| GET | `/user/emoney/transactions` | UserEmoneyController::transactions | 거래 내역 |

### eMoney 관리 (관리자)
| HTTP 메소드 | URI | 컨트롤러 메소드 | 설명 |
|------------|-----|----------------|------|
| GET | `/admin/auth/emoney` | AdminEmoneyController::index | eMoney 관리 |
| GET | `/admin/auth/emoney/charges` | AdminEmoneyController::charges | 충전 요청 목록 |
| POST | `/admin/auth/emoney/charges/{id}/approve` | AdminEmoneyController::approveCharge | 충전 승인 |
| POST | `/admin/auth/emoney/charges/{id}/reject` | AdminEmoneyController::rejectCharge | 충전 거부 |
| GET | `/admin/auth/emoney/withdrawals` | AdminEmoneyController::withdrawals | 출금 요청 목록 |
| POST | `/admin/auth/emoney/withdrawals/{id}/approve` | AdminEmoneyController::approveWithdrawal | 출금 승인 |
| GET | `/admin/auth/emoney/statistics` | AdminEmoneyController::statistics | eMoney 통계 |

### 리워드 프로그램
| HTTP 메소드 | URI | 컨트롤러 메소드 | 설명 |
|------------|-----|----------------|------|
| GET | `/user/rewards` | UserRewardController::index | 리워드 대시보드 |
| POST | `/user/rewards/daily-check` | UserRewardController::dailyCheck | 출석 체크 |
| GET | `/user/rewards/missions` | UserRewardController::missions | 미션 목록 |
| POST | `/user/rewards/missions/{id}/claim` | UserRewardController::claimMission | 미션 보상 수령 |
| GET | `/user/referrals` | UserRewardController::referrals | 추천인 목록 |

## 🎮 컨트롤러

### UserPointController
**위치**: `/jiny/auth/App/Http/Controllers/UserPointController.php`

#### transfer(Request $request)
```php
// 요청 파라미터
'recipient_email' => ['required', 'email', 'exists:users,email'],
'amount' => ['required', 'integer', 'min:100'],
'password' => ['required', 'current_password']

// 처리
1. 잔액 확인
2. 수신자 확인
3. 트랜잭션 처리
4. 알림 발송
```

### AdminEmoneyController
**위치**: `/jiny/auth/App/Http/Controllers/Admin/AdminEmoneyController.php`

#### approveCharge(Request $request, $id)
```php
// 처리 로직
1. 충전 요청 확인
2. 결제 검증
3. eMoney 지급
4. 영수증 발행
5. 사용자 알림
```

## 💾 데이터베이스 테이블

### user_points
| 컬럼명 | 타입 | 설명 |
|--------|------|------|
| id | BIGINT | Primary Key |
| user_id | BIGINT | 사용자 ID |
| balance | INT | 현재 잔액 |
| total_earned | INT | 총 적립 포인트 |
| total_used | INT | 총 사용 포인트 |
| total_expired | INT | 총 만료 포인트 |

### point_transactions
| 컬럼명 | 타입 | 설명 |
|--------|------|------|
| id | BIGINT | Primary Key |
| user_id | BIGINT | 사용자 ID |
| type | ENUM | earn/use/expire/cancel |
| amount | INT | 포인트 금액 |
| balance_after | INT | 거래 후 잔액 |
| reason | VARCHAR | 사유 |
| reference_type | VARCHAR | 참조 유형 |
| reference_id | BIGINT | 참조 ID |
| expires_at | DATE | 만료일 |
| created_at | TIMESTAMP | 거래일 |

### user_emoney
| 컬럼명 | 타입 | 설명 |
|--------|------|------|
| id | BIGINT | Primary Key |
| user_id | BIGINT | 사용자 ID |
| balance | DECIMAL(15,2) | 현재 잔액 |
| hold_amount | DECIMAL(15,2) | 보류 금액 |
| total_charged | DECIMAL(15,2) | 총 충전액 |
| total_withdrawn | DECIMAL(15,2) | 총 출금액 |

### emoney_transactions
| 컬럼명 | 타입 | 설명 |
|--------|------|------|
| id | BIGINT | Primary Key |
| user_id | BIGINT | 사용자 ID |
| type | ENUM | charge/use/transfer/withdraw |
| amount | DECIMAL(15,2) | 금액 |
| fee | DECIMAL(10,2) | 수수료 |
| balance_after | DECIMAL(15,2) | 거래 후 잔액 |
| status | ENUM | pending/completed/failed/cancelled |
| payment_method | VARCHAR | 결제 수단 |
| transaction_id | VARCHAR | 거래 ID |
| created_at | TIMESTAMP | 거래일 |

### reward_missions
| 컬럼명 | 타입 | 설명 |
|--------|------|------|
| id | BIGINT | Primary Key |
| name | VARCHAR | 미션명 |
| description | TEXT | 설명 |
| type | VARCHAR | 미션 유형 |
| target_value | INT | 목표값 |
| reward_type | ENUM | point/emoney |
| reward_amount | INT | 보상 금액 |
| start_date | DATE | 시작일 |
| end_date | DATE | 종료일 |
| is_active | BOOLEAN | 활성화 여부 |

### user_missions
| 컬럼명 | 타입 | 설명 |
|--------|------|------|
| id | BIGINT | Primary Key |
| user_id | BIGINT | 사용자 ID |
| mission_id | BIGINT | 미션 ID |
| progress | INT | 진행도 |
| completed_at | TIMESTAMP | 완료일 |
| claimed_at | TIMESTAMP | 보상 수령일 |

## 💰 포인트 시스템

### 포인트 적립 정책
```php
// config/points.php
return [
    'earning_rules' => [
        'registration' => 1000,        // 회원가입
        'email_verification' => 500,   // 이메일 인증
        'daily_login' => 10,           // 일일 로그인
        'purchase' => 1,               // 구매금액의 1%
        'review' => 100,               // 리뷰 작성
        'referral' => 500,             // 추천인
        'birthday' => 1000             // 생일
    ],
    'expiry' => [
        'enabled' => true,
        'months' => 12                 // 12개월 후 만료
    ],
    'minimum_use' => 100,              // 최소 사용 포인트
    'maximum_hold' => 1000000          // 최대 보유 포인트
];
```

### 포인트 적립 처리
```php
// app/Services/PointService.php
public function earnPoints(User $user, $amount, $reason, $reference = null)
{
    DB::beginTransaction();
    
    try {
        // 포인트 잔액 업데이트
        $userPoint = $user->points()->lockForUpdate()->first();
        $newBalance = $userPoint->balance + $amount;
        
        // 최대 보유 제한 체크
        if ($newBalance > config('points.maximum_hold')) {
            throw new \Exception('Maximum point limit exceeded');
        }
        
        $userPoint->update([
            'balance' => $newBalance,
            'total_earned' => $userPoint->total_earned + $amount
        ]);
        
        // 거래 내역 기록
        $transaction = PointTransaction::create([
            'user_id' => $user->id,
            'type' => 'earn',
            'amount' => $amount,
            'balance_after' => $newBalance,
            'reason' => $reason,
            'reference_type' => $reference ? get_class($reference) : null,
            'reference_id' => $reference ? $reference->id : null,
            'expires_at' => now()->addMonths(config('points.expiry.months'))
        ]);
        
        DB::commit();
        
        // 알림
        $user->notify(new PointsEarned($amount, $reason));
        
        return $transaction;
        
    } catch (\Exception $e) {
        DB::rollback();
        throw $e;
    }
}
```

### 포인트 만료 처리
```php
// app/Console/Commands/ExpirePoints.php
public function handle()
{
    $expiredTransactions = PointTransaction::where('type', 'earn')
        ->where('expires_at', '<', now())
        ->whereNull('expired_at')
        ->get();
    
    foreach ($expiredTransactions as $transaction) {
        $user = $transaction->user;
        $amount = $transaction->getRemainAmount(); // 남은 포인트 계산
        
        if ($amount > 0) {
            // 만료 처리
            $this->pointService->expirePoints($user, $amount, $transaction);
            
            // 사용자 알림
            $user->notify(new PointsExpired($amount));
        }
    }
}
```

## 💳 eMoney 시스템

### eMoney 충전 프로세스
```php
// app/Services/EmoneyService.php
public function requestCharge(User $user, $amount, $paymentMethod)
{
    // 충전 요청 생성
    $chargeRequest = EmoneyChargeRequest::create([
        'user_id' => $user->id,
        'amount' => $amount,
        'payment_method' => $paymentMethod,
        'status' => 'pending',
        'request_id' => Str::uuid()
    ]);
    
    // 결제 처리 (PG사 연동)
    $payment = $this->processPayment($chargeRequest);
    
    if ($payment['success']) {
        // 자동 승인 (검증된 결제)
        $this->approveCharge($chargeRequest, $payment['transaction_id']);
    } else {
        $chargeRequest->update([
            'status' => 'failed',
            'failure_reason' => $payment['error']
        ]);
    }
    
    return $chargeRequest;
}

public function approveCharge($chargeRequest, $transactionId)
{
    DB::beginTransaction();
    
    try {
        // eMoney 지급
        $userEmoney = $chargeRequest->user->emoney()->lockForUpdate()->first();
        $newBalance = $userEmoney->balance + $chargeRequest->amount;
        
        $userEmoney->update([
            'balance' => $newBalance,
            'total_charged' => $userEmoney->total_charged + $chargeRequest->amount
        ]);
        
        // 거래 내역
        EmoneyTransaction::create([
            'user_id' => $chargeRequest->user_id,
            'type' => 'charge',
            'amount' => $chargeRequest->amount,
            'balance_after' => $newBalance,
            'status' => 'completed',
            'payment_method' => $chargeRequest->payment_method,
            'transaction_id' => $transactionId
        ]);
        
        // 충전 요청 상태 업데이트
        $chargeRequest->update([
            'status' => 'approved',
            'approved_at' => now(),
            'transaction_id' => $transactionId
        ]);
        
        DB::commit();
        
        // 알림 및 영수증 발송
        $chargeRequest->user->notify(new EmoneyCharged($chargeRequest));
        
    } catch (\Exception $e) {
        DB::rollback();
        throw $e;
    }
}
```

### eMoney 송금
```php
public function transfer(User $sender, User $recipient, $amount, $message = null)
{
    DB::beginTransaction();
    
    try {
        // 송금 한도 체크
        $dailyTransferred = $this->getDailyTransferAmount($sender);
        if ($dailyTransferred + $amount > 1000000) {
            throw new \Exception('Daily transfer limit exceeded');
        }
        
        // 잔액 체크
        $senderEmoney = $sender->emoney()->lockForUpdate()->first();
        if ($senderEmoney->balance < $amount) {
            throw new \Exception('Insufficient balance');
        }
        
        // 송금자 차감
        $senderEmoney->balance -= $amount;
        $senderEmoney->save();
        
        // 수신자 추가
        $recipientEmoney = $recipient->emoney()->lockForUpdate()->first();
        $recipientEmoney->balance += $amount;
        $recipientEmoney->save();
        
        // 거래 내역 (송금자)
        EmoneyTransaction::create([
            'user_id' => $sender->id,
            'type' => 'transfer_out',
            'amount' => -$amount,
            'balance_after' => $senderEmoney->balance,
            'status' => 'completed',
            'related_user_id' => $recipient->id,
            'message' => $message
        ]);
        
        // 거래 내역 (수신자)
        EmoneyTransaction::create([
            'user_id' => $recipient->id,
            'type' => 'transfer_in',
            'amount' => $amount,
            'balance_after' => $recipientEmoney->balance,
            'status' => 'completed',
            'related_user_id' => $sender->id,
            'message' => $message
        ]);
        
        DB::commit();
        
        // 알림
        $recipient->notify(new EmoneyReceived($sender, $amount, $message));
        
    } catch (\Exception $e) {
        DB::rollback();
        throw $e;
    }
}
```

## 🎁 리워드 프로그램

### 출석 체크 시스템
```php
// app/Services/RewardService.php
public function dailyCheck(User $user)
{
    // 오늘 이미 체크했는지 확인
    $lastCheck = $user->dailyChecks()->latest()->first();
    
    if ($lastCheck && $lastCheck->created_at->isToday()) {
        throw new \Exception('Already checked in today');
    }
    
    // 연속 출석 계산
    $consecutiveDays = 1;
    if ($lastCheck && $lastCheck->created_at->isYesterday()) {
        $consecutiveDays = $lastCheck->consecutive_days + 1;
    }
    
    // 보상 계산
    $baseReward = 10;
    $bonusReward = min($consecutiveDays * 5, 100); // 최대 100포인트
    $totalReward = $baseReward + $bonusReward;
    
    // 출석 기록
    DailyCheck::create([
        'user_id' => $user->id,
        'consecutive_days' => $consecutiveDays,
        'reward_amount' => $totalReward
    ]);
    
    // 포인트 지급
    $this->pointService->earnPoints($user, $totalReward, 'daily_check');
    
    // 연속 출석 보너스 (7일, 30일)
    if ($consecutiveDays == 7) {
        $this->pointService->earnPoints($user, 100, 'weekly_streak_bonus');
    } elseif ($consecutiveDays == 30) {
        $this->pointService->earnPoints($user, 500, 'monthly_streak_bonus');
    }
    
    return [
        'consecutive_days' => $consecutiveDays,
        'reward' => $totalReward
    ];
}
```

### 미션 시스템
```php
public function checkMissionProgress(User $user, $eventType, $eventData)
{
    $activeMissions = RewardMission::where('is_active', true)
        ->where('type', $eventType)
        ->where('start_date', '<=', now())
        ->where('end_date', '>=', now())
        ->get();
    
    foreach ($activeMissions as $mission) {
        $userMission = $user->missions()
            ->firstOrCreate(['mission_id' => $mission->id]);
        
        // 이미 완료된 미션 스킵
        if ($userMission->completed_at) {
            continue;
        }
        
        // 진행도 업데이트
        $progress = $this->calculateProgress($mission, $user, $eventData);
        $userMission->progress = min($progress, $mission->target_value);
        
        // 미션 완료 체크
        if ($userMission->progress >= $mission->target_value) {
            $userMission->completed_at = now();
            
            // 자동 보상 지급 설정인 경우
            if ($mission->auto_claim) {
                $this->claimMissionReward($userMission);
            }
        }
        
        $userMission->save();
    }
}
```

## 📦 Request/Response 예시

### 포인트 양도
```http
POST /user/points/transfer
Content-Type: application/json

{
    "recipient_email": "friend@example.com",
    "amount": 1000,
    "password": "current_password"
}
```

### eMoney 충전
```http
POST /user/emoney/charge
Content-Type: application/json

{
    "amount": 50000,
    "payment_method": "credit_card",
    "card_number": "****-****-****-1234",
    "return_url": "https://example.com/payment/complete"
}
```

응답:
```json
{
    "success": true,
    "charge_request_id": "chr_abc123",
    "payment_url": "https://pg.example.com/pay/xyz789",
    "amount": 50000,
    "status": "pending"
}
```

## 🚨 주의사항

1. **포인트 정책**
   - 명확한 적립/사용 규정
   - 만료 정책 사전 고지
   - 부정 적립 모니터링

2. **eMoney 보안**
   - 이중 인증 필수
   - 거래 한도 설정
   - 이상 거래 감지

3. **정산 처리**
   - 정확한 세금 계산
   - 환불 정책 명시
   - 거래 내역 보관 (5년)

4. **법적 준수**
   - 전자금융거래법 준수
   - 개인정보보호법 준수
   - 자금세탁방지 규정

## 📝 활용 예시

### 시나리오 1: 구매 시 포인트 적립
```php
// 주문 완료 후
event(new OrderCompleted($order));

// EventListener
public function handle(OrderCompleted $event)
{
    $order = $event->order;
    $user = $order->user;
    
    // 등급별 적립률 적용
    $pointRate = $user->grade->point_rate ?? 0.01;
    $points = floor($order->total_amount * $pointRate);
    
    // 포인트 적립
    app(PointService::class)->earnPoints(
        $user,
        $points,
        'purchase',
        $order
    );
}
```

### 시나리오 2: 포인트로 eMoney 전환
```php
public function convertPointsToEmoney(User $user, $points)
{
    // 전환 비율 (1:1 또는 설정값)
    $conversionRate = 1.0;
    $emoneyAmount = $points * $conversionRate;
    
    DB::beginTransaction();
    
    try {
        // 포인트 차감
        app(PointService::class)->usePoints($user, $points, 'convert_to_emoney');
        
        // eMoney 추가
        app(EmoneyService::class)->addEmoney($user, $emoneyAmount, 'point_conversion');
        
        DB::commit();
        
        return [
            'points_used' => $points,
            'emoney_added' => $emoneyAmount
        ];
        
    } catch (\Exception $e) {
        DB::rollback();
        throw $e;
    }
}
```