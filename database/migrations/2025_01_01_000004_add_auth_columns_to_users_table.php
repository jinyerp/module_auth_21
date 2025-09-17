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
            // Basic auth fields
            $table->string('username')->unique()->nullable()->after('name');
            $table->boolean('is_active')->default(true)->after('password');
            $table->boolean('is_suspended')->default(false)->after('is_active');
            $table->timestamp('suspended_until')->nullable()->after('is_suspended');
            $table->string('suspension_reason')->nullable()->after('suspended_until');
            
            // Password management
            $table->timestamp('password_changed_at')->nullable()->after('password');
            $table->json('password_history')->nullable()->after('password_changed_at');
            $table->boolean('must_change_password')->default(false)->after('password_history');
            
            // 2FA fields
            $table->string('two_factor_secret')->nullable()->after('password');
            $table->string('two_factor_recovery_codes')->nullable()->after('two_factor_secret');
            $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_recovery_codes');
            
            // Profile fields
            $table->string('phone')->nullable();
            $table->string('phone_verified_at')->nullable();
            $table->string('avatar')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('timezone')->nullable()->default('UTC');
            $table->string('locale')->nullable()->default('en');
            
            // Login tracking
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();
            $table->integer('login_count')->default(0);
            
            // Social login
            $table->json('social_accounts')->nullable();
            
            // User preferences
            $table->json('preferences')->nullable();
            $table->json('metadata')->nullable();
            
            // Soft deletes (if not already present)
            if (!Schema::hasColumn('users', 'deleted_at')) {
                $table->softDeletes();
            }
            
            // Indexes
            $table->index('is_active');
            $table->index('is_suspended');
            $table->index('last_login_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'username',
                'is_active',
                'is_suspended',
                'suspended_until',
                'suspension_reason',
                'password_changed_at',
                'password_history',
                'must_change_password',
                'two_factor_secret',
                'two_factor_recovery_codes',
                'two_factor_confirmed_at',
                'phone',
                'phone_verified_at',
                'avatar',
                'date_of_birth',
                'gender',
                'timezone',
                'locale',
                'last_login_at',
                'last_login_ip',
                'login_count',
                'social_accounts',
                'preferences',
                'metadata',
            ]);
        });
    }
};