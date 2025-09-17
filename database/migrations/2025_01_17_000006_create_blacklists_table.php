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
        Schema::create('blacklists', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['email', 'ip', 'domain', 'phone', 'keyword']);
            $table->string('value');
            $table->string('reason')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_whitelist')->default(false); // true면 화이트리스트
            $table->timestamp('expires_at')->nullable(); // 만료 시간
            $table->foreignId('added_by')->nullable()->constrained('users')->onDelete('set null');
            $table->integer('match_count')->default(0); // 매칭된 횟수
            $table->timestamp('last_matched_at')->nullable(); // 마지막 매칭 시간
            $table->json('metadata')->nullable(); // 추가 정보
            $table->timestamps();
            
            $table->unique(['type', 'value']);
            $table->index(['type', 'is_active', 'is_whitelist']);
            $table->index('value');
            $table->index('expires_at');
        });
        
        // 블랙리스트 로그 테이블
        Schema::create('blacklist_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blacklist_id')->nullable()->constrained()->onDelete('set null');
            $table->string('action'); // blocked, allowed, added, removed, updated
            $table->string('type'); // email, ip, domain, phone, keyword
            $table->string('value');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->json('context')->nullable(); // 추가 컨텍스트 정보
            $table->timestamps();
            
            $table->index(['action', 'created_at']);
            $table->index(['type', 'value']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blacklist_logs');
        Schema::dropIfExists('blacklists');
    }
};