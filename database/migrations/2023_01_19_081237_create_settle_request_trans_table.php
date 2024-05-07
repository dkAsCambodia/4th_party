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
        Schema::create('settle_request_trans', function (Blueprint $table) {
            $table->id();
            $table->integer('merchant_id')->nullable();
            $table->integer('agent_id')->nullable();
            $table->integer('settle_request_id')->nullable();
            $table->integer('payment_detail_id')->nullable();
            // $table->enum('status', ['settled','settleRequest','cancel'])->default('settleRequest');
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
        Schema::dropIfExists('settle_request_trans');
    }
};
