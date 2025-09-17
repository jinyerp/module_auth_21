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
        Schema::create('user_emoney', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            //사용자
            $table->string('user_id')->nullable();
            $table->string('email')->nullable();
            $table->string('name')->nullable();

            $table->string('currency')->nullable(); // 기준통화
            $table->string('balance')->nullable(); // 잔액
            $table->string('point')->nullable(); // 포인트

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
        Schema::dropIfExists('user_emoney');
    }
};
