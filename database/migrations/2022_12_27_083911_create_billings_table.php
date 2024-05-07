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
        Schema::create('billings', function (Blueprint $table) {
            $table->id();
            $table->integer('merchant_id')->nullable();
            $table->string('merchant_name')->nullable();
            $table->integer('agent_id')->nullable();
            $table->string('agent_name')->nullable();
            $table->enum('withdraw_switch', ['turn_on', 'closure'])->default('turn_on');
            $table->string('week_allow_withdrawals')->nullable();
            $table->string('withdrawal_start_time')->nullable();
            $table->string('withdrawal_end_time')->nullable();
            $table->string('daily_withdrawals')->nullable();
            $table->string('max_daily_withdrawals')->nullable();
            $table->string('single_max_withdrawal')->nullable();
            $table->string('single_min_withdrawal')->nullable();
            $table->enum('settlement_fee_type', ['percentage_fee', 'fixed_fee'])->default('percentage_fee');
            $table->string('settlement_fee_ratio')->nullable();
            $table->string('single_transaction_fee_limit')->nullable();
            $table->enum('payment_method', ['d0_arrives', 'd1_account'])->default('d0_arrives')->nullable();
            $table->string('status')->nullable();
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
        Schema::dropIfExists('billings');
    }
};
