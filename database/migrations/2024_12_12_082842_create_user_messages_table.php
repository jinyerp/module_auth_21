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
        Schema::create('user_messages', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('enable')->nullable();
            $table->string('notice')->nullable();

            // 사용자 id 연동
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('email')->nullable();
            $table->string('name')->nullable();

            $table->string('from_email')->nullable();
            $table->string('from_name')->nullable();
            $table->string('from_user_id')->nullable();

            $table->string('subject')->nullable();
            $table->text('message')->nullable();

            $table->string('status')->nullable();
            $table->string('label')->nullable();

            // 확인시간
            $table->string('readed_at')->nullable();


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_messages');
    }
};
