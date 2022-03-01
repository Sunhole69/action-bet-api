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
        Schema::create('sport_leagues', function (Blueprint $table) {
            $table->id();
            $table->integer('group_id');
            $table->integer('champ_id');
            $table->string('name');
            $table->string('country_code')->nullable();
            $table->integer('event_counts');
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
        Schema::dropIfExists('sport_leagues');
    }
};
