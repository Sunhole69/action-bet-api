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
        Schema::create('ante_post_sport_groups', function (Blueprint $table) {
            $table->id();
            $table->integer('sport_id');
            $table->string('group_id');
            $table->string('name');
            $table->string('country_code')->nullable();
            $table->integer('events_count');
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
        Schema::dropIfExists('ante_post_sport_groups');
    }
};
