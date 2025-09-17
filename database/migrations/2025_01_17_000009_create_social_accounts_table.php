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
        // 소셜 계정 테이블
        Schema::create('social_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('provider', 30); // google, facebook, github, naver, kakao
            $table->string('provider_user_id');
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('avatar')->nullable();
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->timestamp('token_expires_at')->nullable();
            $table->json('provider_data')->nullable();
            $table->timestamps();
            
            $table->unique(['provider', 'provider_user_id']);
            $table->index(['user_id', 'provider']);
        });
        
        // OAuth 공급자 설정 테이블
        Schema::create('oauth_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 30)->unique(); // google, facebook, github, naver, kakao
            $table->string('display_name', 50);
            $table->boolean('enabled')->default(false);
            $table->string('client_id')->nullable();
            $table->string('client_secret')->nullable();
            $table->string('redirect_uri')->nullable();
            $table->json('scopes')->nullable();
            $table->json('settings')->nullable();
            $table->integer('priority')->default(0);
            $table->timestamps();
            
            $table->index('enabled');
            $table->index('priority');
        });
        
        // 소셜 로그인 로그 테이블
        Schema::create('social_login_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('provider', 30);
            $table->string('provider_user_id')->nullable();
            $table->string('email')->nullable();
            $table->enum('action', ['login', 'register', 'connect', 'disconnect', 'failed']);
            $table->string('status')->default('success');
            $table->text('error_message')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['provider', 'action']);
            $table->index('created_at');
        });
        
        // 기본 OAuth 공급자 데이터 삽입
        DB::table('oauth_providers')->insert([
            [
                'name' => 'google',
                'display_name' => 'Google',
                'enabled' => false,
                'scopes' => json_encode(['openid', 'profile', 'email']),
                'priority' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'facebook',
                'display_name' => 'Facebook',
                'enabled' => false,
                'scopes' => json_encode(['email', 'public_profile']),
                'priority' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'github',
                'display_name' => 'GitHub',
                'enabled' => false,
                'scopes' => json_encode(['user:email']),
                'priority' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'naver',
                'display_name' => 'Naver',
                'enabled' => false,
                'scopes' => json_encode(['profile', 'email']),
                'priority' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'kakao',
                'display_name' => 'Kakao',
                'enabled' => false,
                'scopes' => json_encode(['profile_nickname', 'profile_image', 'account_email']),
                'priority' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_login_logs');
        Schema::dropIfExists('oauth_providers');
        Schema::dropIfExists('social_accounts');
    }
};