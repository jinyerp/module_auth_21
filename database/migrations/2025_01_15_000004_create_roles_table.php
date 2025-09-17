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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('display_name')->nullable();
            $table->string('description')->nullable();
            $table->integer('level')->default(0);
            $table->json('permissions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false); // System roles cannot be deleted
            $table->integer('priority')->default(0);
            $table->json('meta')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('name');
            $table->index('is_active');
            $table->index('level');
            $table->index('priority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};