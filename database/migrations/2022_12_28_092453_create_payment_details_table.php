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
        Schema::create('payment_details', function (Blueprint $table) {
            $table->id();
            $table->string('merchant_code')->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('fourth_party_transection')->nullable();
            $table->decimal('amount', 8, 2)->nullable();
            $table->string('customer_name')->nullable();
            $table->string('callback_url')->nullable();
            $table->string('payment_channel')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_source')->nullable();
            $table->string('product_id')->nullable();
            $table->string('order_id')->nullable();
            $table->string('order_date')->nullable();
            $table->string('order_status')->nullable();
            $table->string('Currency')->nullable();
            $table->string('TransId')->nullable();
            // $table->string('Status')->nullable();
            $table->string('ErrDesc')->nullable();
            //$table->string('payment_status')->default('pending');
            $table->integer('payment_status')->nullable();
            $table->enum('merchant_settle_status', ['settled', 'unsettled', 'settleRequest', 'cancel'])->default('unsettled');
            $table->enum('agent_settle_status', ['settled', 'unsettled', 'settleRequest', 'cancel'])->default('unsettled');
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
        Schema::dropIfExists('payment_details');
    }
};
