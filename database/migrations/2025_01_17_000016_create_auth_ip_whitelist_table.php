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
        // IP 화이트리스트 테이블
        Schema::create('auth_ip_whitelist', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 45)->unique(); // IPv4/IPv6 지원
            $table->string('description')->nullable();
            $table->foreignId('added_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('ip_address');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auth_ip_whitelist');
    }
};