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
        Schema::create('auth_login_attempts', function (Blueprint $table) {
            $table->id();
            $table->string('email')->nullable();
            $table->string('username')->nullable();
            $table->string('ip_address', 45);
            $table->text('user_agent')->nullable();
            $table->string('browser')->nullable();
            $table->string('platform')->nullable();
            $table->enum('status', ['success', 'failed', 'blocked'])->default('failed');
            $table->string('failure_reason')->nullable();
            $table->integer('attempt_count')->default(1);
            $table->timestamp('locked_until')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index('email');
            $table->index('username');
            $table->index('ip_address');
            $table->index('status');
            $table->index('created_at');
            $table->index(['email', 'ip_address']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auth_login_attempts');
    }
};