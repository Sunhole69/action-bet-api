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
        Schema::create('player_bet_combine_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_bet_combines_id')->constrained('player_bet_combines');
            $table->string('search_code');
            $table->string('sign_key');
            $table->double('rank');
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
        Schema::dropIfExists('player_bet_combine_events');
    }
};
