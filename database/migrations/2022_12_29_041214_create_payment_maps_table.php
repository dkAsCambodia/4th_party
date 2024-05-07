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
        Schema::create('payment_maps', function (Blueprint $table) {
            $table->id();
            $table->decimal('map_value', 8, 2)->nullable();
            $table->string('product_id')->nullable();
            //$table->foreignId('payment_url_id')->constrained('payment_urls');
            $table->foreignId('merchant_id')->constrained('merchants');
            //$table->foreignId('channel_id')->constrained('payment_channels');
            $table->foreignId('payment_method_id')->constrained('payment_methods');
            $table->string('gateway_payment_channel_id');
            //$table->foreignId('method_id')->constrained('payment_methods');
            //$table->foreignId('source_id')->constrained('payment_sources');

            $table->integer('agent_commission')->nullable();
            $table->integer('merchant_commission')->nullable();
            $table->string('channel_mode')->nullable();
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
        Schema::dropIfExists('payment_maps');
    }
};
