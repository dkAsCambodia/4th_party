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
        Schema::create('gateway_account_methods', function (Blueprint $table) {
            $table->id();
            // $table->string('url_name')->nullable();
            $table->string('method_id')->nullable();
            // $table->foreignId('method_id')->constrained('payment_methods');
            $table->foreignId('gateway_account_id')->constrained('gateway_accounts');

            $table->string('payment_link')->nullable();
            $table->string('merchant_key')->nullable()->comment('Clint Secret');
            $table->string('merchant_code')->nullable()->comment('Seller Code');
            $table->string('sign_pre')->nullable()->comment('API Secret Key');

            $table->string('username')->nullable()->comment('For Kess');
            $table->string('password')->nullable()->comment('For Kess');
            $table->string('client_id')->nullable()->comment('For Kess');

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
        Schema::dropIfExists('gateway_account_methods');
    }
};
