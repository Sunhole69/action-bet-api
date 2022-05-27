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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->enum('channel', ['web', 'mobile'])->nullable();
            $table->bigInteger('amount');
            $table->enum('payment_method', ['monnify', 'paystack', 'flutterwave'])->nullable();
            $table->string('reference', 500)->nullable();
            $table->string('transaction_code', 500)->nullable();
            $table->string('trxref', 500)->nullable();
            $table->enum('payment_type', ['Withdrawal', 'Deposit', 'Padiwin_bonus', 'Player_credit']);
            $table->enum('status', ['pending', 'Success', 'Failed']);
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
        Schema::dropIfExists('transactions');
    }
};
