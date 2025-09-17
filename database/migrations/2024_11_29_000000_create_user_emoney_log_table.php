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
        Schema::create('user_emoney_log', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            //사용자
            $table->string('email')->nullable();
            $table->string('user_id')->nullable();

            // 유형
            $table->string('type')->nullable();
            $table->string('currency')->nullable(); // 기준통화
            $table->string('currency_rate')->nullable(); // 환율

            $table->string('balance')->nullable();
            $table->string('balance_currency')->nullable();
            $table->string('balance_currency_rate')->nullable();

            $table->string('point')->nullable(); // 포인트

            $table->string('withdraw')->nullable(); // 출금
            $table->string('withdraw_currency')->nullable();
            $table->string('withdraw_currency_rate')->nullable();

            $table->string('deposit')->nullable(); // 입금
            $table->string('deposit_currency')->nullable();
            $table->string('deposit_currency_rate')->nullable();


            $table->string('trans')->nullable(); // 거래 테이블
            $table->string('trans_id')->nullable(); // 거래id

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
        Schema::dropIfExists('user_emoney_log');
    }
};
