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
        // 긴급 점검 로그
        Schema::create('auth_maintenance_logs', function (Blueprint $table) {
            $table->id();
            $table->enum('action', ['activated', 'deactivated']);
            $table->text('message')->nullable();
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            $table->index('action');
            $table->index('created_at');
        });
        
        // 긴급 상황 로그
        Schema::create('auth_emergency_logs', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // login_blocked, kill_sessions 등
            $table->string('action');
            $table->text('reason')->nullable();
            $table->json('data')->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            $table->index('type');
            $table->index('created_at');
        });
        
        // 긴급 알림
        Schema::create('auth_emergency_alerts', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['email', 'sms', 'both']);
            $table->enum('priority', ['low', 'medium', 'high', 'critical']);
            $table->string('subject');
            $table->text('message');
            $table->string('target'); // all, admins, users, specific
            $table->integer('sent_count')->default(0);
            $table->foreignId('sent_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            $table->index('priority');
            $table->index('created_at');
        });
        
        // 보안 사고
        Schema::create('auth_security_incidents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('type', ['breach', 'attack', 'vulnerability', 'suspicious', 'other']);
            $table->enum('severity', ['low', 'medium', 'high', 'critical']);
            $table->enum('status', ['open', 'investigating', 'contained', 'resolved', 'closed'])
                ->default('open');
            $table->text('description');
            $table->json('affected_systems')->nullable();
            $table->text('resolution')->nullable();
            $table->text('root_cause')->nullable();
            $table->text('preventive_measures')->nullable();
            $table->text('lessons_learned')->nullable();
            $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            
            $table->index('type');
            $table->index('severity');
            $table->index('status');
            $table->index('created_at');
        });
        
        // 사고 영향받은 사용자
        Schema::create('auth_incident_affected_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incident_id')->constrained('auth_security_incidents')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            
            $table->unique(['incident_id', 'user_id']);
            $table->index('incident_id');
            $table->index('user_id');
        });
        
        // 사고 조치 내역
        Schema::create('auth_incident_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incident_id')->constrained('auth_security_incidents')->cascadeOnDelete();
            $table->text('action');
            $table->enum('action_type', [
                'immediate', 'investigation', 'mitigation', 
                'containment', 'recovery', 'resolution', 
                'update', 'automatic', 'other'
            ]);
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            $table->index('incident_id');
            $table->index('action_type');
            $table->index('created_at');
        });
        
        // 사고 타임라인
        Schema::create('auth_incident_timeline', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incident_id')->constrained('auth_security_incidents')->cascadeOnDelete();
            $table->string('event');
            $table->text('description')->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('occurred_at');
            $table->timestamps();
            
            $table->index('incident_id');
            $table->index('occurred_at');
        });
        
        // 에러 로그 (시스템 체크용)
        Schema::create('auth_error_logs', function (Blueprint $table) {
            $table->id();
            $table->string('level'); // error, warning, info
            $table->string('category')->nullable();
            $table->text('message');
            $table->text('context')->nullable();
            $table->string('file')->nullable();
            $table->integer('line')->nullable();
            $table->text('trace')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            
            $table->index('level');
            $table->index('category');
            $table->index('created_at');
        });
        
        // 등급 변경 이력 (대량 작업용)
        Schema::create('auth_grade_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('old_grade')->nullable();
            $table->string('new_grade');
            $table->string('reason')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auth_grade_histories');
        Schema::dropIfExists('auth_error_logs');
        Schema::dropIfExists('auth_incident_timeline');
        Schema::dropIfExists('auth_incident_actions');
        Schema::dropIfExists('auth_incident_affected_users');
        Schema::dropIfExists('auth_security_incidents');
        Schema::dropIfExists('auth_emergency_alerts');
        Schema::dropIfExists('auth_emergency_logs');
        Schema::dropIfExists('auth_maintenance_logs');
    }
};