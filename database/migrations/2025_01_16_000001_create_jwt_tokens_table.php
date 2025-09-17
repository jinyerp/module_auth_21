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
        Schema::create('jwt_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained('accounts')->onDelete('cascade');
            $table->text('access_token');
            $table->text('refresh_token');
            $table->timestamp('expires_at');
            $table->timestamp('refresh_expires_at');
            $table->timestamp('invalidated_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('device_name')->nullable();
            $table->timestamps();
            
            $table->index('account_id');
            $table->index('expires_at');
            $table->index('invalidated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jwt_tokens');
    }
};