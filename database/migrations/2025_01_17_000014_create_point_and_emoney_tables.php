<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // 포인트 테이블
        Schema::create('auth_user_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->decimal('balance', 15, 2)->default(0); // 현재 포인트 잔액
            $table->decimal('total_earned', 15, 2)->default(0); // 총 획득 포인트
            $table->decimal('total_used', 15, 2)->default(0); // 총 사용 포인트
            $table->decimal('total_expired', 15, 2)->default(0); // 총 만료 포인트
            $table->timestamp('last_earned_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('balance');
        });
        
        // 포인트 거래 내역
        Schema::create('auth_point_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 20); // earn, use, expire, adjust
            $table->string('action', 50); // purchase, review, signup, birthday, admin_adjust, etc
            $table->decimal('amount', 15, 2);
            $table->decimal('balance_before', 15, 2);
            $table->decimal('balance_after', 15, 2);
            $table->string('description');
            $table->string('reference_type', 50)->nullable(); // order, review, etc
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->date('expire_date')->nullable();
            $table->boolean('is_expired')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'created_at']);
            $table->index(['type', 'action']);
            $table->index('expire_date');
            $table->index(['reference_type', 'reference_id']);
        });
        
        // eMoney 지갑
        Schema::create('auth_emoney_wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('currency', 3)->default('KRW'); // 통화
            $table->decimal('balance', 15, 2)->default(0); // 현재 잔액
            $table->decimal('total_deposited', 15, 2)->default(0); // 총 입금액
            $table->decimal('total_withdrawn', 15, 2)->default(0); // 총 출금액
            $table->decimal('total_spent', 15, 2)->default(0); // 총 사용액
            $table->decimal('pending_withdrawal', 15, 2)->default(0); // 출금 대기 금액
            $table->boolean('is_active')->default(true);
            $table->boolean('is_locked')->default(false); // 잠금 상태
            $table->string('locked_reason')->nullable();
            $table->timestamp('locked_at')->nullable();
            $table->timestamp('last_deposit_at')->nullable();
            $table->timestamp('last_withdrawal_at')->nullable();
            $table->timestamp('last_transaction_at')->nullable();
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('currency');
            $table->index('is_active');
        });
        
        // eMoney 거래 내역
        Schema::create('auth_emoney_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('transaction_id')->unique(); // 거래 ID
            $table->string('type', 20); // deposit, withdraw, transfer, payment, refund
            $table->string('method', 30)->nullable(); // bank_transfer, card, virtual_account
            $table->decimal('amount', 15, 2);
            $table->decimal('fee', 15, 2)->default(0); // 수수료
            $table->decimal('net_amount', 15, 2); // 실제 금액 (amount - fee)
            $table->decimal('balance_before', 15, 2);
            $table->decimal('balance_after', 15, 2);
            $table->string('currency', 3)->default('KRW');
            $table->string('status', 20)->default('pending'); // pending, processing, completed, failed, cancelled
            $table->string('description');
            $table->string('reference_type', 50)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->foreignId('from_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('payment_info')->nullable(); // 결제 정보
            $table->json('metadata')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->string('failure_reason')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'created_at']);
            $table->index('transaction_id');
            $table->index('type');
            $table->index('status');
            $table->index(['reference_type', 'reference_id']);
        });
        
        // 은행 계좌 정보
        Schema::create('auth_bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('bank_code', 10); // 은행 코드
            $table->string('bank_name', 50); // 은행명
            $table->string('account_number'); // 계좌번호 (암호화)
            $table->string('account_holder'); // 예금주 (암호화)
            $table->boolean('is_default')->default(false); // 기본 계좌
            $table->boolean('is_verified')->default(false); // 인증 여부
            $table->timestamp('verified_at')->nullable();
            $table->string('verification_method')->nullable(); // 1won, document
            $table->json('verification_data')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['user_id', 'is_default']);
            $table->index('is_active');
        });
        
        // 출금 신청
        Schema::create('auth_withdrawal_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('request_id')->unique();
            $table->decimal('amount', 15, 2);
            $table->decimal('fee', 15, 2)->default(0);
            $table->decimal('net_amount', 15, 2); // 실수령액
            $table->string('currency', 3)->default('KRW');
            $table->foreignId('bank_account_id')->nullable()->constrained('auth_bank_accounts')->nullOnDelete();
            $table->string('bank_code', 10)->nullable();
            $table->string('bank_name', 50)->nullable();
            $table->string('account_number')->nullable();
            $table->string('account_holder')->nullable();
            $table->string('status', 20)->default('pending'); // pending, approved, processing, completed, rejected, cancelled
            $table->text('reject_reason')->nullable();
            $table->text('admin_note')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index('request_id');
            $table->index('status');
            $table->index('created_at');
        });
        
        // 입금 내역
        Schema::create('auth_deposit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('deposit_id')->unique();
            $table->decimal('amount', 15, 2);
            $table->decimal('fee', 15, 2)->default(0);
            $table->decimal('net_amount', 15, 2);
            $table->string('currency', 3)->default('KRW');
            $table->string('method', 30); // bank_transfer, card, virtual_account, etc
            $table->string('status', 20)->default('pending'); // pending, confirmed, failed, cancelled
            $table->string('bank_name', 50)->nullable();
            $table->string('depositor_name')->nullable();
            $table->json('payment_info')->nullable();
            $table->string('transaction_id')->nullable();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->text('admin_note')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index('deposit_id');
            $table->index('status');
            $table->index('created_at');
        });
        
        // 은행 마스터 데이터
        Schema::create('auth_banks', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique(); // 은행 코드
            $table->string('name', 50); // 은행명
            $table->string('english_name', 50)->nullable();
            $table->string('swift_code', 20)->nullable();
            $table->string('logo')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->index('code');
            $table->index('is_active');
        });
        
        // 통화 마스터 데이터
        Schema::create('auth_currencies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 3)->unique(); // ISO 4217 코드
            $table->string('name', 50);
            $table->string('symbol', 5);
            $table->integer('decimal_places')->default(2);
            $table->decimal('exchange_rate', 10, 4)->default(1); // 기준 통화 대비 환율
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('code');
            $table->index('is_active');
        });
        
        // 환율 로그
        Schema::create('auth_currency_logs', function (Blueprint $table) {
            $table->id();
            $table->string('currency_code', 3);
            $table->decimal('old_rate', 10, 4);
            $table->decimal('new_rate', 10, 4);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('rate_date');
            $table->timestamps();
            
            $table->index(['currency_code', 'rate_date']);
        });
        
        // 기본 데이터 삽입
        $this->seedDefaultData();
    }
    
    private function seedDefaultData()
    {
        // 한국 은행 데이터
        $banks = [
            ['code' => '001', 'name' => '한국은행', 'english_name' => 'Bank of Korea', 'sort_order' => 1],
            ['code' => '002', 'name' => '산업은행', 'english_name' => 'KDB', 'sort_order' => 2],
            ['code' => '003', 'name' => '기업은행', 'english_name' => 'IBK', 'sort_order' => 3],
            ['code' => '004', 'name' => 'KB국민은행', 'english_name' => 'KB', 'sort_order' => 4],
            ['code' => '007', 'name' => '수협은행', 'english_name' => 'Suhyup', 'sort_order' => 5],
            ['code' => '008', 'name' => '수출입은행', 'english_name' => 'EXIM', 'sort_order' => 6],
            ['code' => '011', 'name' => 'NH농협은행', 'english_name' => 'NH', 'sort_order' => 7],
            ['code' => '012', 'name' => '농협중앙회', 'english_name' => 'NACF', 'sort_order' => 8],
            ['code' => '020', 'name' => '우리은행', 'english_name' => 'Woori', 'sort_order' => 9],
            ['code' => '023', 'name' => 'SC제일은행', 'english_name' => 'SC', 'sort_order' => 10],
            ['code' => '027', 'name' => '한국씨티은행', 'english_name' => 'Citi', 'sort_order' => 11],
            ['code' => '031', 'name' => '대구은행', 'english_name' => 'DGB', 'sort_order' => 12],
            ['code' => '032', 'name' => '부산은행', 'english_name' => 'BNK', 'sort_order' => 13],
            ['code' => '034', 'name' => '광주은행', 'english_name' => 'KJB', 'sort_order' => 14],
            ['code' => '035', 'name' => '제주은행', 'english_name' => 'Jeju', 'sort_order' => 15],
            ['code' => '037', 'name' => '전북은행', 'english_name' => 'JB', 'sort_order' => 16],
            ['code' => '039', 'name' => '경남은행', 'english_name' => 'BNK', 'sort_order' => 17],
            ['code' => '045', 'name' => '새마을금고', 'english_name' => 'MG', 'sort_order' => 18],
            ['code' => '048', 'name' => '신협', 'english_name' => 'CU', 'sort_order' => 19],
            ['code' => '071', 'name' => '우체국', 'english_name' => 'Post Office', 'sort_order' => 20],
            ['code' => '081', 'name' => '하나은행', 'english_name' => 'Hana', 'sort_order' => 21],
            ['code' => '088', 'name' => '신한은행', 'english_name' => 'Shinhan', 'sort_order' => 22],
            ['code' => '089', 'name' => '케이뱅크', 'english_name' => 'K Bank', 'sort_order' => 23],
            ['code' => '090', 'name' => '카카오뱅크', 'english_name' => 'Kakao Bank', 'sort_order' => 24],
            ['code' => '092', 'name' => '토스뱅크', 'english_name' => 'Toss Bank', 'sort_order' => 25],
        ];
        
        foreach ($banks as $bank) {
            $bank['is_active'] = true;
            $bank['created_at'] = now();
            $bank['updated_at'] = now();
            DB::table('auth_banks')->insert($bank);
        }
        
        // 통화 데이터
        $currencies = [
            ['code' => 'KRW', 'name' => '대한민국 원', 'symbol' => '₩', 'decimal_places' => 0, 'exchange_rate' => 1],
            ['code' => 'USD', 'name' => '미국 달러', 'symbol' => '$', 'decimal_places' => 2, 'exchange_rate' => 1320],
            ['code' => 'EUR', 'name' => '유로', 'symbol' => '€', 'decimal_places' => 2, 'exchange_rate' => 1440],
            ['code' => 'JPY', 'name' => '일본 엔', 'symbol' => '¥', 'decimal_places' => 0, 'exchange_rate' => 8.5],
            ['code' => 'CNY', 'name' => '중국 위안', 'symbol' => '¥', 'decimal_places' => 2, 'exchange_rate' => 182],
            ['code' => 'GBP', 'name' => '영국 파운드', 'symbol' => '£', 'decimal_places' => 2, 'exchange_rate' => 1670],
        ];
        
        foreach ($currencies as $currency) {
            $currency['is_active'] = true;
            $currency['created_at'] = now();
            $currency['updated_at'] = now();
            DB::table('auth_currencies')->insert($currency);
        }
    }
    
    public function down()
    {
        Schema::dropIfExists('auth_currency_logs');
        Schema::dropIfExists('auth_currencies');
        Schema::dropIfExists('auth_banks');
        Schema::dropIfExists('auth_deposit_logs');
        Schema::dropIfExists('auth_withdrawal_requests');
        Schema::dropIfExists('auth_bank_accounts');
        Schema::dropIfExists('auth_emoney_transactions');
        Schema::dropIfExists('auth_emoney_wallets');
        Schema::dropIfExists('auth_point_transactions');
        Schema::dropIfExists('auth_user_points');
    }
};