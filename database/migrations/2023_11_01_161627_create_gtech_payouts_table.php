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
        Schema::create('gtech_payouts', function (Blueprint $table) {
            $table->id();
            $table->string('client_ip')->nullable();
            $table->string('payout_api_token')->nullable();
            $table->string('vstore_id')->nullable();
            $table->string('action')->nullable();
            $table->string('source')->nullable();
            $table->string('source_url')->nullable();
            $table->string('source_type')->nullable();
            $table->string('price')->nullable();
            $table->string('curr')->nullable();
            $table->string('product_name')->nullable();
            $table->string('remarks')->nullable();
            $table->string('narration')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_addressline_1')->nullable();
            $table->string('customer_addressline_2')->nullable();
            $table->string('customer_city')->nullable();
            $table->string('customer_state')->nullable();
            $table->string('customer_country')->nullable();
            $table->string('customer_zip')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('customer_bank_name')->nullable();
            $table->string('customer_bank_code')->nullable();
            $table->string('customer_account_number')->nullable();
            $table->string('payout_membercode')->nullable();
            $table->string('payout_request_id')->nullable();
            $table->string('payout_notify_url')->nullable();
            $table->string('payout_success_url')->nullable();
            $table->string('payout_error_url')->nullable();
            $table->string('payout_all')->nullable();
            $table->string('payout_aar')->nullable();
            $table->string('orderid')->nullable();
            $table->string('orderstatus')->nullable();
            $table->string('orderremarks')->nullable();
            $table->string('status')->nullable();
            $table->string('pdate')->nullable();
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
        Schema::dropIfExists('gtech_payouts');
    }
};
