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
        Schema::create('login_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained('accounts')->cascadeOnDelete();
            $table->string('ip_address', 45);
            $table->string('user_agent')->nullable();
            $table->string('browser')->nullable();
            $table->string('browser_version')->nullable();
            $table->string('platform')->nullable();
            $table->string('device')->nullable();
            $table->string('device_type')->nullable();
            $table->string('location')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->enum('status', ['success', 'failed', 'blocked'])->default('success');
            $table->string('failure_reason')->nullable();
            $table->string('session_id')->nullable();
            $table->timestamp('login_at');
            $table->timestamp('logout_at')->nullable();
            $table->integer('duration')->nullable(); // in seconds
            $table->json('meta')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('account_id');
            $table->index('ip_address');
            $table->index('status');
            $table->index('login_at');
            $table->index('session_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('login_histories');
    }
};