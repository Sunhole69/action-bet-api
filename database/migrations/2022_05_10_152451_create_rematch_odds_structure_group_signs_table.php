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
        Schema::create('rematch_odds_structure_group_signs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('odds_structure_group_id');
            $table->string('sign_key');
            $table->string('sign_name');
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
        Schema::dropIfExists('rematch_odds_structure_group_signs');
    }
};
