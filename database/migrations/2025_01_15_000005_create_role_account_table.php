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
        Schema::create('role_account', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('account_id')->constrained('accounts')->cascadeOnDelete();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('assigned_by')->nullable()->constrained('accounts')->nullOnDelete();
            $table->string('reason')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            
            // Unique constraint to prevent duplicate role assignments
            $table->unique(['role_id', 'account_id']);
            
            // Indexes
            $table->index('role_id');
            $table->index('account_id');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_account');
    }
};