<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 사용자 메시지 테이블
        Schema::create('user_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('recipient_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 20)->default('message'); // message, system, notification, announcement
            $table->string('subject');
            $table->text('content');
            $table->string('priority', 10)->default('normal'); // low, normal, high, urgent
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->boolean('is_starred')->default(false);
            $table->boolean('is_archived')->default(false);
            $table->boolean('sender_deleted')->default(false);
            $table->boolean('recipient_deleted')->default(false);
            $table->json('attachments')->nullable(); // 첨부파일 정보
            $table->json('metadata')->nullable(); // 추가 메타데이터
            $table->timestamp('expires_at')->nullable(); // 메시지 만료일
            $table->timestamps();
            
            // 인덱스
            $table->index('sender_id');
            $table->index('recipient_id');
            $table->index('type');
            $table->index('is_read');
            $table->index('created_at');
            $table->index(['recipient_id', 'is_read']);
            $table->index(['sender_id', 'sender_deleted']);
            $table->index(['recipient_id', 'recipient_deleted']);
        });
        
        // 메시지 스레드 (대화 그룹)
        Schema::create('message_threads', function (Blueprint $table) {
            $table->id();
            $table->string('subject')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('last_message_at');
            $table->integer('message_count')->default(0);
            $table->json('participants')->nullable(); // 참여자 ID 목록
            $table->timestamps();
            
            $table->index('created_by');
            $table->index('last_message_at');
        });
        
        // 스레드 메시지
        Schema::create('thread_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('thread_id')->constrained('message_threads')->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->text('content');
            $table->json('attachments')->nullable();
            $table->boolean('is_edited')->default(false);
            $table->timestamp('edited_at')->nullable();
            $table->timestamps();
            
            $table->index(['thread_id', 'created_at']);
            $table->index('sender_id');
        });
        
        // 스레드 참여자
        Schema::create('thread_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('thread_id')->constrained('message_threads')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('last_read_at')->nullable();
            $table->integer('unread_count')->default(0);
            $table->boolean('is_muted')->default(false);
            $table->timestamp('muted_until')->nullable();
            $table->timestamps();
            
            $table->unique(['thread_id', 'user_id']);
            $table->index('user_id');
            $table->index(['user_id', 'unread_count']);
        });
        
        // 메시지 템플릿
        Schema::create('message_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category', 50)->nullable();
            $table->string('subject');
            $table->text('content');
            $table->json('variables')->nullable(); // 사용 가능한 변수 목록
            $table->boolean('is_active')->default(true);
            $table->integer('usage_count')->default(0);
            $table->timestamps();
            
            $table->index('category');
            $table->index('is_active');
        });
        
        // 차단된 사용자
        Schema::create('message_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('blocked_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('reason')->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'blocked_user_id']);
            $table->index('blocked_user_id');
        });
        
        // 메시지 알림 설정
        Schema::create('message_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('email_enabled')->default(true);
            $table->boolean('push_enabled')->default(true);
            $table->boolean('sms_enabled')->default(false);
            $table->json('email_types')->nullable(); // 이메일 알림 받을 메시지 타입
            $table->json('push_types')->nullable(); // 푸시 알림 받을 메시지 타입
            $table->json('quiet_hours')->nullable(); // 방해 금지 시간대
            $table->timestamps();
            
            $table->unique('user_id');
        });
        
        // 대량 메시지 발송 로그
        Schema::create('bulk_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->string('subject');
            $table->text('content');
            $table->string('target_type', 30); // all, group, role, custom
            $table->json('target_criteria')->nullable(); // 대상 조건
            $table->integer('total_recipients')->default(0);
            $table->integer('sent_count')->default(0);
            $table->integer('failed_count')->default(0);
            $table->string('status', 20)->default('pending'); // pending, processing, completed, failed
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index('sender_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bulk_messages');
        Schema::dropIfExists('message_notifications');
        Schema::dropIfExists('message_blocks');
        Schema::dropIfExists('message_templates');
        Schema::dropIfExists('thread_participants');
        Schema::dropIfExists('thread_messages');
        Schema::dropIfExists('message_threads');
        Schema::dropIfExists('user_messages');
    }
};