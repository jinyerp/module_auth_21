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
        // 사용자 주소록 테이블
        Schema::create('user_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['home', 'office', 'shipping', 'billing', 'other'])->default('home');
            $table->string('name', 100);
            $table->string('phone', 20);
            $table->string('address_line_1');
            $table->string('address_line_2')->nullable();
            $table->string('city', 100);
            $table->string('state', 100)->nullable();
            $table->string('postal_code', 20);
            $table->string('country', 2); // ISO 3166-1 alpha-2
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            
            $table->index(['user_id', 'type']);
            $table->index('is_default');
        });
        
        // 아바타 변경 이력 테이블
        Schema::create('user_avatar_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('avatar_path');
            $table->foreignId('changed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('deleted')->default(false);
            $table->timestamps();
            
            $table->index(['user_id', 'created_at']);
        });
        
        // 사용자 활동 로그 테이블
        Schema::create('user_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('action', 50);
            $table->text('description')->nullable();
            $table->foreignId('admin_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'action']);
            $table->index('created_at');
        });
        
        // 사용자 추가정보 테이블
        Schema::create('user_additional_info', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('company', 100)->nullable();
            $table->string('job_title', 100)->nullable();
            $table->string('department', 100)->nullable();
            $table->string('website')->nullable();
            $table->json('social_media')->nullable();
            $table->json('preferences')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->unique('user_id');
        });
        
        // 사용자 커스텀 필드 테이블
        Schema::create('user_custom_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('field_key', 50);
            $table->text('field_value')->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'field_key']);
            $table->index('field_key');
        });
        
        // users 테이블에 프로필 필드 추가
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone', 20)->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'birthdate')) {
                $table->date('birthdate')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('users', 'gender')) {
                $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('birthdate');
            }
            if (!Schema::hasColumn('users', 'bio')) {
                $table->text('bio')->nullable()->after('gender');
            }
            if (!Schema::hasColumn('users', 'country_id')) {
                $table->unsignedBigInteger('country_id')->nullable()->after('bio');
            }
            if (!Schema::hasColumn('users', 'language')) {
                $table->string('language', 5)->default('ko')->after('country_id');
            }
            if (!Schema::hasColumn('users', 'timezone')) {
                $table->string('timezone', 50)->default('Asia/Seoul')->after('language');
            }
            
            // 인덱스
            $table->index('phone');
            $table->index('birthdate');
            $table->index('country_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['phone']);
            $table->dropIndex(['birthdate']);
            $table->dropIndex(['country_id']);
            
            $table->dropColumn([
                'phone', 'birthdate', 'gender', 'bio',
                'country_id', 'language', 'timezone'
            ]);
        });
        
        Schema::dropIfExists('user_custom_fields');
        Schema::dropIfExists('user_additional_info');
        Schema::dropIfExists('user_activity_logs');
        Schema::dropIfExists('user_avatar_history');
        Schema::dropIfExists('user_addresses');
    }
};