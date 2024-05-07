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
        Schema::create('gateway_payment_channels', function (Blueprint $table) {
            $table->id();
            $table->string('channel_id')->nullable();
            $table->string('channel_description')->nullable();
            $table->string('gateway_account_id')->nullable();
            $table->string('gateway_account_method_id')->nullable();
            $table->string('daily_max_limit')->nullable();
            $table->string('max_limit_per_trans')->nullable();
            $table->string('daily_max_trans')->nullable();
            $table->string('status')->nullable();
            $table->string('risk_control')->nullable();
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
        Schema::dropIfExists('gateway_payment_channels');
    }
};
