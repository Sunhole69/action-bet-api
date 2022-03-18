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
        Schema::create('player_bets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users'); //Player user_id
            $table->enum('bet_type', ['single', 'multiple', 'split', 'combined']);
            $table->bigInteger('amount');
            $table->enum('status', ['lost', 'won', 'rejected', 'waiting']);
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
        Schema::dropIfExists('player_bets');
    }
};
