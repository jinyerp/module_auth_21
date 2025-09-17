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
        Schema::create('user_oauth_providers', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('enable')->nullable();
            $table->string('name')->nullable();

            $table->string('provider')->nullable();
            $table->string('client_id')->nullable();
            $table->string('client_secret')->nullable();

            $table->string('redirect_url')->nullable();
            $table->string('callback_url')->nullable();
            $table->string('logout_url')->nullable();

            $table->string('icon')->nullable();
            $table->string('color')->nullable();

            $table->integer('users')->default(0); // 사용자 수

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_oauth_providers');
    }
};
