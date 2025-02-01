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
        Schema::create('settle_requests', function (Blueprint $table) {
            $table->id();
            $table->string('settlement_trans_id')->nullable();     // main transactionID
            $table->string('fourth_party_transection')->nullable();
            $table->string('merchant_track_id')->nullable();
            $table->string('gateway_name')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();  
            $table->longText('callback_url')->nullable();
            $table->string('Currency')->nullable();
            $table->string('total')->nullable();
            $table->string('net_amount')->nullable();
            $table->string('mdr_fee_amount')->nullable();
            $table->integer('merchant_id')->nullable();
            $table->string('merchant_code')->nullable();
            $table->integer('agent_id')->nullable();
            $table->string('product_id')->nullable();
            $table->string('payment_channel')->nullable();
            $table->string('payment_method')->nullable();
            $table->longText('message')->nullable();
            $table->longText('api_response')->nullable();
            $table->string('customer_bank_name')->nullable();
            $table->string('bank_code')->nullable();
            $table->string('customer_account_number')->nullable();
            $table->string('status')->nullable()->default('pending');
            $table->string('ip_address')->nullable();
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
        Schema::dropIfExists('settle_requests');
    }
};
