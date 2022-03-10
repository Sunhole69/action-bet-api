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
        Schema::create('special_sport_event_lists', function (Blueprint $table) {
            $table->id();
            $table->string('league_id');
            $table->string('search_code');
            $table->string('name');
            $table->string('startdate')->nullable();
            $table->integer('multiplicity');
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
        Schema::dropIfExists('special_sport_event_lists');
    }
};
