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
        Schema::create('user_emoney_deposit', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            //사용자
            $table->string('email')->nullable();
            $table->string('user_id')->nullable();

            // 출금금액
            $table->string('amount')->nullable();
            // 출금통화
            $table->string('currency')->nullable();
            // 출금통화 환율
            $table->string('currency_rate')->nullable();

            // 출금은행
            $table->string('bank_id')->nullable();
            $table->string('bank')->nullable();
            $table->string('account')->nullable();
            $table->string('owner')->nullable();

            $table->text('description')->nullable();

            $table->string('log_id')->nullable();

            // 출금승인
            $table->string('checked')->nullable(); // 확인
            $table->string('checked_at')->nullable(); // 확인일시

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
        Schema::dropIfExists('user_emoney_deposit');
    }
};
