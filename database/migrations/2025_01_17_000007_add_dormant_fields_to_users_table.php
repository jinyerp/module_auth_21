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
        Schema::table('users', function (Blueprint $table) {
            // 휴면계정 관련 필드
            $table->boolean('is_dormant')->default(false)->after('remember_token');
            $table->timestamp('dormant_at')->nullable()->after('is_dormant');
            $table->timestamp('dormant_notified_at')->nullable()->after('dormant_at');
            $table->integer('dormant_notification_count')->default(0)->after('dormant_notified_at');
            $table->timestamp('dormant_scheduled_delete_at')->nullable()->after('dormant_notification_count');
            $table->string('dormant_reason')->nullable()->after('dormant_scheduled_delete_at');
            
            // 인덱스
            $table->index(['is_dormant', 'dormant_at']);
            $table->index('dormant_scheduled_delete_at');
        });
        
        // 휴면계정 활성화 토큰 테이블
        Schema::create('dormant_activation_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('token')->unique();
            $table->string('email');
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            
            $table->index(['token', 'expires_at']);
            $table->index('user_id');
        });
        
        // 휴면계정 로그 테이블
        Schema::create('dormant_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('action'); // marked_dormant, activated, extended, deleted, notified
            $table->text('description')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->foreignId('admin_id')->nullable()->constrained('users')->onDelete('set null');
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'action']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dormant_logs');
        Schema::dropIfExists('dormant_activation_tokens');
        
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['is_dormant', 'dormant_at']);
            $table->dropIndex(['dormant_scheduled_delete_at']);
            
            $table->dropColumn([
                'is_dormant',
                'dormant_at',
                'dormant_notified_at',
                'dormant_notification_count',
                'dormant_scheduled_delete_at',
                'dormant_reason'
            ]);
        });
    }
};