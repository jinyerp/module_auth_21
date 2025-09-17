<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 이메일 템플릿 테이블
        Schema::create('auth_email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('subject');
            $table->text('body');
            $table->string('category', 50)->nullable();
            $table->json('variables')->nullable(); // 사용 가능한 변수 목록
            $table->string('locale', 10)->default('ko');
            $table->boolean('is_active')->default(true);
            $table->integer('usage_count')->default(0);
            $table->timestamps();
            
            $table->index('name');
            $table->index('category');
            $table->index('locale');
            $table->index('is_active');
        });
        
        // 이메일 발송 로그
        Schema::create('auth_email_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('to');
            $table->string('cc')->nullable();
            $table->string('bcc')->nullable();
            $table->string('from')->nullable();
            $table->string('subject');
            $table->text('body');
            $table->string('template_name')->nullable();
            $table->string('status', 20)->default('pending'); // pending, sent, failed, bounced
            $table->string('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('clicked_at')->nullable();
            $table->string('message_id')->nullable();
            $table->json('headers')->nullable();
            $table->json('attachments')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('to');
            $table->index('status');
            $table->index('template_name');
            $table->index('created_at');
        });
        
        // SMS 템플릿 테이블
        Schema::create('auth_sms_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('title')->nullable();
            $table->text('content');
            $table->string('category', 50)->nullable();
            $table->json('variables')->nullable();
            $table->string('sender', 20)->nullable(); // 발신번호
            $table->boolean('is_active')->default(true);
            $table->integer('usage_count')->default(0);
            $table->timestamps();
            
            $table->index('name');
            $table->index('category');
            $table->index('is_active');
        });
        
        // SMS 발송 로그
        Schema::create('auth_sms_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('to', 20);
            $table->string('from', 20)->nullable();
            $table->text('content');
            $table->string('template_name')->nullable();
            $table->string('provider', 30)->nullable(); // twilio, aligo, toast, etc
            $table->string('status', 20)->default('pending'); // pending, sent, delivered, failed
            $table->string('error_message')->nullable();
            $table->string('message_id')->nullable(); // 외부 서비스 메시지 ID
            $table->decimal('cost', 10, 4)->nullable(); // 발송 비용
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->json('response')->nullable(); // API 응답
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('to');
            $table->index('status');
            $table->index('provider');
            $table->index('template_name');
            $table->index('created_at');
        });
        
        // SMS 발신번호 관리
        Schema::create('auth_sms_senders', function (Blueprint $table) {
            $table->id();
            $table->string('number', 20)->unique();
            $table->string('name')->nullable();
            $table->string('description')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('provider', 30)->nullable();
            $table->json('provider_config')->nullable();
            $table->timestamps();
            
            $table->index('number');
            $table->index('is_default');
            $table->index('is_active');
        });
        
        // 알림 설정 (이메일/SMS 수신 동의)
        Schema::create('auth_notification_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('email_enabled')->default(true);
            $table->boolean('email_marketing')->default(false);
            $table->boolean('email_security')->default(true);
            $table->boolean('email_updates')->default(true);
            $table->boolean('sms_enabled')->default(true);
            $table->boolean('sms_marketing')->default(false);
            $table->boolean('sms_security')->default(true);
            $table->boolean('sms_updates')->default(true);
            $table->json('quiet_hours')->nullable(); // 방해 금지 시간
            $table->json('preferences')->nullable(); // 추가 설정
            $table->timestamps();
            
            $table->unique('user_id');
        });
        
        // 대량 발송 작업
        Schema::create('auth_bulk_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type', 10); // email, sms
            $table->string('name');
            $table->string('subject')->nullable(); // 이메일용
            $table->text('content');
            $table->string('template_name')->nullable();
            $table->string('target_type', 30); // all, group, role, custom
            $table->json('target_criteria')->nullable();
            $table->integer('total_recipients')->default(0);
            $table->integer('sent_count')->default(0);
            $table->integer('failed_count')->default(0);
            $table->string('status', 20)->default('pending'); // pending, processing, completed, failed, cancelled
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index('type');
            $table->index('status');
            $table->index('created_by');
            $table->index('created_at');
        });
        
        // 기본 이메일 템플릿 삽입
        $emailTemplates = [
            [
                'name' => 'welcome',
                'subject' => '{{ site_name }}에 오신 것을 환영합니다!',
                'body' => '<h2>안녕하세요 {{ user_name }}님!</h2><p>{{ site_name }}에 가입해 주셔서 감사합니다.</p>',
                'category' => 'auth',
                'variables' => json_encode(['user_name', 'site_name']),
                'locale' => 'ko',
                'is_active' => true,
            ],
            [
                'name' => 'password_reset',
                'subject' => '비밀번호 재설정 안내',
                'body' => '<h2>비밀번호 재설정</h2><p>아래 링크를 클릭하여 비밀번호를 재설정하세요:</p><p><a href="{{ reset_link }}">비밀번호 재설정</a></p>',
                'category' => 'auth',
                'variables' => json_encode(['user_name', 'reset_link']),
                'locale' => 'ko',
                'is_active' => true,
            ],
            [
                'name' => 'email_verification',
                'subject' => '이메일 인증을 완료해주세요',
                'body' => '<h2>이메일 인증</h2><p>아래 링크를 클릭하여 이메일을 인증해주세요:</p><p><a href="{{ verify_link }}">이메일 인증</a></p>',
                'category' => 'auth',
                'variables' => json_encode(['user_name', 'verify_link']),
                'locale' => 'ko',
                'is_active' => true,
            ],
            [
                'name' => '2fa_code',
                'subject' => '2단계 인증 코드',
                'body' => '<h2>2단계 인증</h2><p>인증 코드: <strong>{{ code }}</strong></p><p>이 코드는 {{ expire_minutes }}분 후에 만료됩니다.</p>',
                'category' => 'security',
                'variables' => json_encode(['code', 'expire_minutes']),
                'locale' => 'ko',
                'is_active' => true,
            ],
            [
                'name' => 'login_alert',
                'subject' => '새로운 로그인 알림',
                'body' => '<h2>새로운 로그인 감지</h2><p>다음 위치에서 새로운 로그인이 감지되었습니다:</p><ul><li>IP: {{ ip_address }}</li><li>브라우저: {{ browser }}</li><li>시간: {{ login_time }}</li></ul>',
                'category' => 'security',
                'variables' => json_encode(['ip_address', 'browser', 'login_time']),
                'locale' => 'ko',
                'is_active' => true,
            ],
        ];
        
        foreach ($emailTemplates as $template) {
            DB::table('auth_email_templates')->insert(array_merge($template, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
        
        // 기본 SMS 템플릿 삽입
        $smsTemplates = [
            [
                'name' => 'verification_code',
                'title' => '인증번호',
                'content' => '[{{ site_name }}] 인증번호는 {{ code }}입니다. {{ expire_minutes }}분 내에 입력해주세요.',
                'category' => 'auth',
                'variables' => json_encode(['site_name', 'code', 'expire_minutes']),
                'is_active' => true,
            ],
            [
                'name' => '2fa_sms',
                'title' => '2단계 인증',
                'content' => '[{{ site_name }}] 2단계 인증 코드: {{ code }}',
                'category' => 'security',
                'variables' => json_encode(['site_name', 'code']),
                'is_active' => true,
            ],
            [
                'name' => 'password_reset_sms',
                'title' => '비밀번호 재설정',
                'content' => '[{{ site_name }}] 비밀번호 재설정 코드: {{ code }}. 10분 내에 입력하세요.',
                'category' => 'auth',
                'variables' => json_encode(['site_name', 'code']),
                'is_active' => true,
            ],
            [
                'name' => 'login_alert_sms',
                'title' => '로그인 알림',
                'content' => '[{{ site_name }}] 새로운 로그인이 감지되었습니다. IP: {{ ip_address }}',
                'category' => 'security',
                'variables' => json_encode(['site_name', 'ip_address']),
                'is_active' => true,
            ],
        ];
        
        foreach ($smsTemplates as $template) {
            DB::table('auth_sms_templates')->insert(array_merge($template, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auth_bulk_notifications');
        Schema::dropIfExists('auth_notification_settings');
        Schema::dropIfExists('auth_sms_senders');
        Schema::dropIfExists('auth_sms_logs');
        Schema::dropIfExists('auth_sms_templates');
        Schema::dropIfExists('auth_email_logs');
        Schema::dropIfExists('auth_email_templates');
    }
};