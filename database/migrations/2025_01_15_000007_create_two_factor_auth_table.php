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
        Schema::create('two_factor_auth', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->unique()->constrained('accounts')->cascadeOnDelete();
            $table->enum('method', ['totp', 'sms', 'email', 'app'])->default('totp');
            $table->text('secret')->nullable();
            $table->text('recovery_codes')->nullable();
            $table->boolean('is_enabled')->default(false);
            $table->timestamp('enabled_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->integer('failed_attempts')->default(0);
            $table->timestamp('locked_until')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('email')->nullable();
            $table->string('backup_email')->nullable();
            $table->json('trusted_devices')->nullable();
            $table->json('settings')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('account_id');
            $table->index('method');
            $table->index('is_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('two_factor_auth');
    }
};