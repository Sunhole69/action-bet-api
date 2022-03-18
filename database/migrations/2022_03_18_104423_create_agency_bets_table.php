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
        Schema::create('agency_bets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users'); //Agency user_id
            $table->string('player_username');
            $table->enum('bet_type', ['single', 'multiple', 'split', 'combined']);
            $table->bigInteger('amount');
            $table->enum('status', ['lost', 'won', 'rejected', 'placed'])->default('placed');
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
        Schema::dropIfExists('agency_bets');
    }
};
