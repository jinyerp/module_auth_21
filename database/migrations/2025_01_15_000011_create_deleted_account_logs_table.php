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
        Schema::create('deleted_account_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('original_id');
            $table->unsignedBigInteger('account_id');
            $table->string('action', 100);
            $table->string('description')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->json('meta')->nullable();
            $table->string('status', 50)->default('success');
            $table->text('error_message')->nullable();
            $table->timestamp('performed_at')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamp('deleted_at');
            $table->timestamps();
            
            // Indexes
            $table->index('original_id');
            $table->index('account_id');
            $table->index('deleted_by');
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deleted_account_logs');
    }
};