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
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('code', 2)->unique(); // ISO 3166-1 alpha-2
            $table->string('code3', 3)->unique(); // ISO 3166-1 alpha-3
            $table->string('numeric_code', 3)->nullable(); // ISO 3166-1 numeric
            $table->string('name');
            $table->string('native_name')->nullable();
            $table->string('capital')->nullable();
            $table->string('region')->nullable();
            $table->string('subregion')->nullable();
            $table->string('currency_code', 3)->nullable();
            $table->string('currency_name')->nullable();
            $table->string('currency_symbol')->nullable();
            $table->string('phone_code')->nullable();
            $table->json('languages')->nullable();
            $table->string('flag_emoji')->nullable();
            $table->string('flag_svg')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('timezone')->nullable();
            $table->json('timezones')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(0);
            $table->json('meta')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('code');
            $table->index('code3');
            $table->index('name');
            $table->index('is_active');
            $table->index('display_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};