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
        Schema::create('dormant_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->unique()->constrained('accounts')->cascadeOnDelete();
            $table->timestamp('last_activity_at');
            $table->timestamp('dormant_at');
            $table->timestamp('notified_at')->nullable();
            $table->integer('notification_count')->default(0);
            $table->timestamp('scheduled_deletion_at')->nullable();
            $table->enum('status', ['dormant', 'notified', 'reactivated', 'deleted'])->default('dormant');
            $table->string('reason')->nullable();
            $table->json('backup_data')->nullable(); // Store important data before deletion
            $table->timestamp('reactivated_at')->nullable();
            $table->foreignId('reactivated_by')->nullable()->constrained('accounts')->nullOnDelete();
            $table->string('reactivation_reason')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('account_id');
            $table->index('status');
            $table->index('dormant_at');
            $table->index('scheduled_deletion_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dormant_accounts');
    }
};