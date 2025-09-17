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
        Schema::create('user_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('device_type')->nullable(); // desktop, mobile, tablet
            $table->string('browser')->nullable();
            $table->string('browser_version')->nullable();
            $table->string('platform')->nullable(); // Windows, MacOS, Linux, iOS, Android
            $table->string('platform_version')->nullable();
            $table->string('device_name')->nullable();
            $table->string('location')->nullable(); // Country/City from IP
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->timestamp('last_activity');
            $table->timestamp('login_at');
            $table->timestamp('logout_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('remember_token')->default(false);
            $table->json('payload')->nullable(); // Additional session data
            $table->timestamps();
            
            $table->index(['user_id', 'is_active']);
            $table->index('session_id');
            $table->index('last_activity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_sessions');
    }
};