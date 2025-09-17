<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('auth_currency', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('enable')->nullable();
            $table->string('default')->nullable();

            // 유형
            $table->string('type')->nullable();
            $table->string('currency')->nullable(); // 기준통화

            $table->string('unit')->nullable();
            $table->string('rate')->nullable();

            $table->text('description')->nullable();

            $table->string('status')->nullable(); // 상태
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('auth_currency');
    }
};
