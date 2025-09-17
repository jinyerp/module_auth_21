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
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('display_name')->nullable();
            $table->string('description')->nullable();
            $table->integer('level')->default(0);
            $table->integer('min_points')->default(0);
            $table->integer('max_points')->nullable();
            $table->string('badge')->nullable();
            $table->string('color')->nullable();
            $table->json('benefits')->nullable();
            $table->json('restrictions')->nullable();
            $table->decimal('discount_rate', 5, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->integer('priority')->default(0);
            $table->json('meta')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('name');
            $table->index('level');
            $table->index('is_active');
            $table->index('is_default');
            $table->index('priority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};