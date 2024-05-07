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
        Schema::create('payment_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('bank_name')->nullable();
            $table->string('account_name')->nullable();
            $table->string('account_type')->nullable();
            $table->string('account_province')->nullable();
            $table->string('account_number')->nullable();
            $table->string('account_outlet')->nullable();
            $table->string('account_city')->nullable();
            $table->integer('merchant_id')->nullable();
            $table->integer('agent_id')->nullable();
            $table->string('remark')->nullable();
            $table->enum('default', ['yes', 'no'])->default('yes')->nullable();
            $table->enum('status', ['enable', 'disable'])->default('enable')->nullable();
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
        Schema::dropIfExists('payment_accounts');
    }
};
