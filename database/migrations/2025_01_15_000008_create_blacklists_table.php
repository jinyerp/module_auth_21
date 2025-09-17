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
        Schema::create('blacklists', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['email', 'ip', 'phone', 'domain', 'user_agent', 'account'])->index();
            $table->string('value')->index();
            $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->string('reason');
            $table->text('description')->nullable();
            $table->foreignId('added_by')->nullable()->constrained('accounts')->nullOnDelete();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('hit_count')->default(0);
            $table->timestamp('last_hit_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            
            // Unique constraint for type and value
            $table->unique(['type', 'value']);
            
            // Indexes
            $table->index(['type', 'value']);
            $table->index('is_active');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blacklists');
    }
};