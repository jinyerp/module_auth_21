<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // 회원 등급 테이블
        Schema::create('auth_user_grades', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code', 20)->unique(); // bronze, silver, gold, platinum, diamond
            $table->text('description')->nullable();
            $table->integer('level')->default(1); // 등급 레벨 (1-10)
            $table->decimal('discount_rate', 5, 2)->default(0); // 할인율 (%)
            $table->decimal('point_rate', 5, 2)->default(1); // 포인트 적립률 (%)
            $table->decimal('upgrade_amount', 15, 2)->nullable(); // 승급 필요 금액
            $table->integer('upgrade_count')->nullable(); // 승급 필요 구매 횟수
            $table->json('benefits')->nullable(); // 등급별 혜택
            $table->json('permissions')->nullable(); // 등급별 권한
            $table->string('badge_color', 7)->nullable(); // 배지 색상 (#RRGGBB)
            $table->string('icon')->nullable(); // 아이콘 경로
            $table->boolean('is_default')->default(false); // 기본 등급 여부
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->index('code');
            $table->index('level');
            $table->index('is_active');
        });
        
        // 회원 등급 변경 로그
        Schema::create('auth_user_grade_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('from_grade_id')->nullable()->constrained('auth_user_grades')->nullOnDelete();
            $table->foreignId('to_grade_id')->nullable()->constrained('auth_user_grades')->nullOnDelete();
            $table->string('reason')->nullable(); // 변경 사유
            $table->string('changed_by')->nullable(); // admin, system, manual
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamp('changed_at');
            $table->timestamps();
            
            $table->index(['user_id', 'changed_at']);
        });
        
        // 회원 유형 테이블
        Schema::create('auth_user_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code', 20)->unique(); // personal, student, business, partner, reseller
            $table->text('description')->nullable();
            $table->json('required_fields')->nullable(); // 필수 입력 필드
            $table->json('optional_fields')->nullable(); // 선택 입력 필드
            $table->json('permissions')->nullable(); // 유형별 권한
            $table->json('restrictions')->nullable(); // 유형별 제한사항
            $table->boolean('requires_approval')->default(false); // 승인 필요 여부
            $table->boolean('requires_verification')->default(false); // 인증 필요 여부
            $table->string('verification_type')->nullable(); // email, phone, document
            $table->decimal('commission_rate', 5, 2)->nullable(); // 수수료율 (파트너용)
            $table->string('icon')->nullable();
            $table->string('color', 7)->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->index('code');
            $table->index('is_active');
        });
        
        // 회원 유형 변경 로그
        Schema::create('auth_user_type_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('from_type_id')->nullable()->constrained('auth_user_types')->nullOnDelete();
            $table->foreignId('to_type_id')->nullable()->constrained('auth_user_types')->nullOnDelete();
            $table->string('reason')->nullable();
            $table->string('changed_by')->nullable();
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('verification_data')->nullable(); // 인증 데이터
            $table->timestamp('changed_at');
            $table->timestamps();
            
            $table->index(['user_id', 'changed_at']);
        });
        
        // 디바이스 관리 테이블
        Schema::create('auth_user_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('device_id')->unique(); // 디바이스 고유 ID
            $table->string('device_type', 20); // mobile, tablet, desktop, watch, tv
            $table->string('platform', 20)->nullable(); // ios, android, windows, macos, linux
            $table->string('platform_version', 20)->nullable();
            $table->string('browser', 50)->nullable();
            $table->string('browser_version', 20)->nullable();
            $table->string('device_name')->nullable(); // 사용자 정의 이름
            $table->string('model')->nullable(); // 디바이스 모델명
            $table->string('manufacturer')->nullable(); // 제조사
            $table->json('capabilities')->nullable(); // 디바이스 기능 (push, biometric 등)
            $table->string('push_token')->nullable(); // 푸시 알림 토큰
            $table->string('ip_address', 45)->nullable();
            $table->string('location')->nullable();
            $table->boolean('is_trusted')->default(false); // 신뢰된 디바이스
            $table->boolean('is_blocked')->default(false); // 차단 여부
            $table->string('blocked_reason')->nullable();
            $table->timestamp('blocked_at')->nullable();
            $table->foreignId('blocked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('last_active_at')->nullable();
            $table->timestamp('first_seen_at')->nullable();
            $table->integer('login_count')->default(0);
            $table->timestamps();
            
            $table->index(['user_id', 'is_blocked']);
            $table->index('device_id');
            $table->index('device_type');
            $table->index('last_active_at');
        });
        
        // 디바이스 로그인 로그
        Schema::create('auth_device_login_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained('auth_user_devices')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('ip_address', 45);
            $table->string('location')->nullable();
            $table->string('status', 20); // success, failed, blocked
            $table->string('failure_reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('logged_at');
            $table->timestamps();
            
            $table->index(['device_id', 'logged_at']);
            $table->index(['user_id', 'logged_at']);
            $table->index('status');
        });
        
        // users 테이블에 컬럼 추가
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'grade_id')) {
                $table->foreignId('grade_id')->nullable()->after('id')
                    ->constrained('auth_user_grades')->nullOnDelete();
            }
            if (!Schema::hasColumn('users', 'user_type_id')) {
                $table->foreignId('user_type_id')->nullable()->after('grade_id')
                    ->constrained('auth_user_types')->nullOnDelete();
            }
            if (!Schema::hasColumn('users', 'total_purchase_amount')) {
                $table->decimal('total_purchase_amount', 15, 2)->default(0)->after('email');
            }
            if (!Schema::hasColumn('users', 'total_purchase_count')) {
                $table->integer('total_purchase_count')->default(0)->after('total_purchase_amount');
            }
            if (!Schema::hasColumn('users', 'grade_updated_at')) {
                $table->timestamp('grade_updated_at')->nullable();
            }
        });
        
        // 기본 데이터 삽입
        $this->seedDefaultData();
    }
    
    private function seedDefaultData()
    {
        // 기본 회원 등급
        $grades = [
            [
                'name' => 'Bronze',
                'code' => 'bronze',
                'description' => '기본 회원 등급',
                'level' => 1,
                'discount_rate' => 0,
                'point_rate' => 1,
                'upgrade_amount' => 100000,
                'benefits' => json_encode(['기본 혜택']),
                'badge_color' => '#CD7F32',
                'is_default' => true,
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Silver',
                'code' => 'silver',
                'description' => '실버 회원 등급',
                'level' => 2,
                'discount_rate' => 3,
                'point_rate' => 1.5,
                'upgrade_amount' => 500000,
                'benefits' => json_encode(['3% 할인', '1.5배 포인트']),
                'badge_color' => '#C0C0C0',
                'is_active' => true,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Gold',
                'code' => 'gold',
                'description' => '골드 회원 등급',
                'level' => 3,
                'discount_rate' => 5,
                'point_rate' => 2,
                'upgrade_amount' => 1000000,
                'benefits' => json_encode(['5% 할인', '2배 포인트', '무료 배송']),
                'badge_color' => '#FFD700',
                'is_active' => true,
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Platinum',
                'code' => 'platinum',
                'description' => '플래티넘 회원 등급',
                'level' => 4,
                'discount_rate' => 7,
                'point_rate' => 2.5,
                'upgrade_amount' => 3000000,
                'benefits' => json_encode(['7% 할인', '2.5배 포인트', '무료 배송', 'VIP 고객센터']),
                'badge_color' => '#E5E4E2',
                'is_active' => true,
                'sort_order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Diamond',
                'code' => 'diamond',
                'description' => '다이아몬드 회원 등급',
                'level' => 5,
                'discount_rate' => 10,
                'point_rate' => 3,
                'benefits' => json_encode(['10% 할인', '3배 포인트', '무료 배송', 'VIP 고객센터', '전용 이벤트']),
                'badge_color' => '#B9F2FF',
                'is_active' => true,
                'sort_order' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        
        DB::table('auth_user_grades')->insert($grades);
        
        // 기본 회원 유형
        $types = [
            [
                'name' => '개인 회원',
                'code' => 'personal',
                'description' => '일반 개인 회원',
                'required_fields' => json_encode(['name', 'email', 'phone']),
                'is_default' => true,
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '학생 회원',
                'code' => 'student',
                'description' => '학생 인증 회원',
                'required_fields' => json_encode(['name', 'email', 'phone', 'school']),
                'requires_verification' => true,
                'verification_type' => 'document',
                'is_active' => true,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '비즈니스 회원',
                'code' => 'business',
                'description' => '기업 회원',
                'required_fields' => json_encode(['company_name', 'business_number', 'contact_name', 'contact_email', 'contact_phone']),
                'requires_approval' => true,
                'requires_verification' => true,
                'verification_type' => 'document',
                'is_active' => true,
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '파트너',
                'code' => 'partner',
                'description' => '비즈니스 파트너',
                'required_fields' => json_encode(['company_name', 'business_number', 'contact_name', 'contact_email', 'contact_phone']),
                'commission_rate' => 10,
                'requires_approval' => true,
                'requires_verification' => true,
                'verification_type' => 'document',
                'is_active' => true,
                'sort_order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '리셀러',
                'code' => 'reseller',
                'description' => '재판매 파트너',
                'required_fields' => json_encode(['company_name', 'business_number', 'contact_name', 'contact_email', 'contact_phone']),
                'commission_rate' => 15,
                'requires_approval' => true,
                'requires_verification' => true,
                'verification_type' => 'document',
                'is_active' => true,
                'sort_order' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '총판',
                'code' => 'distributor',
                'description' => '총판 파트너',
                'required_fields' => json_encode(['company_name', 'business_number', 'contact_name', 'contact_email', 'contact_phone']),
                'commission_rate' => 20,
                'requires_approval' => true,
                'requires_verification' => true,
                'verification_type' => 'document',
                'is_active' => true,
                'sort_order' => 6,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '에이전트',
                'code' => 'agent',
                'description' => '영업 에이전트',
                'required_fields' => json_encode(['name', 'email', 'phone', 'region']),
                'commission_rate' => 5,
                'requires_approval' => true,
                'is_active' => true,
                'sort_order' => 7,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        
        DB::table('auth_user_types')->insert($types);
    }
    
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['grade_id']);
            $table->dropForeign(['user_type_id']);
            $table->dropColumn([
                'grade_id', 
                'user_type_id',
                'total_purchase_amount',
                'total_purchase_count',
                'grade_updated_at'
            ]);
        });
        
        Schema::dropIfExists('auth_device_login_logs');
        Schema::dropIfExists('auth_user_devices');
        Schema::dropIfExists('auth_user_type_logs');
        Schema::dropIfExists('auth_user_types');
        Schema::dropIfExists('auth_user_grade_logs');
        Schema::dropIfExists('auth_user_grades');
    }
};