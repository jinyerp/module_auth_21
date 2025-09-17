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
        Schema::create('user_oauth', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->bigInteger('user_id')->unsigned();
            $table->string('email')->nullable();

            $table->string('provider')->nullable();
            $table->string('provider_id')->nullable();

            $table->string('oauth_id')->nullable();
            $table->string('avatar')->nullable();

            $table->string('status')->nullable();

            $table->integer('cnt')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_oauth');
    }
};
