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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone')->nullable();
            $table->string('avatar')->nullable();
            
            // Status fields
            $table->enum('status', ['active', 'inactive', 'suspended', 'dormant', 'pending'])->default('active');
            $table->boolean('is_active')->default(true);
            
            // Login tracking
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();
            $table->integer('login_count')->default(0);
            $table->integer('failed_login_count')->default(0);
            
            // Password management
            $table->timestamp('password_changed_at')->nullable();
            $table->timestamp('password_expires_at')->nullable();
            
            // Two-factor authentication
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();
            
            // Relations
            $table->foreignId('grade_id')->nullable()->constrained('grades')->nullOnDelete();
            $table->foreignId('country_id')->nullable()->constrained('countries')->nullOnDelete();
            
            // Additional fields
            $table->string('language', 10)->default('en');
            $table->string('timezone', 50)->default('UTC');
            $table->json('meta')->nullable();
            
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('email');
            $table->index('status');
            $table->index('is_active');
            $table->index('last_login_at');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};