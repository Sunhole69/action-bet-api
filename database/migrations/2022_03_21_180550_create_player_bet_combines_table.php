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
        Schema::create('player_bet_combines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users'); //Player user_id
            $table->bigInteger('amount');
            $table->bigInteger('coupon_id')->nullable();
            $table->string('status')->default('charged');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('player_bet_combines');
    }
};
