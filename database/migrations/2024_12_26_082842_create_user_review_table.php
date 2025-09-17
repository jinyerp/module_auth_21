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
        Schema::create('user_reviews', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            // 사용자 id 연동
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('email')->nullable();
            $table->string('name')->nullable();

            $table->string('item')->nullable(); // 리뷰 아이템
            $table->string('item_id')->nullable(); // 아이템 id

            $table->string('title')->nullable();
            $table->text('review')->nullable();

            $table->integer('likes')->default(0);
            $table->integer('rank')->default(0);
            $table->integer('comments')->default(0);


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_reviews');
    }
};
