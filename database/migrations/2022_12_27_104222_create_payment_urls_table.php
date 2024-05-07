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
        Schema::create('payment_urls', function (Blueprint $table) {
            $table->id();
            $table->string('url_name')->nullable();
            $table->string('url')->nullable();
            $table->string('merchant_key')->nullable();
            $table->string('merchant_code')->nullable();
            $table->string('sign_pre')->nullable();
            $table->foreignId('channel_id')->constrained('payment_channels');
            $table->foreignId('method_id')->constrained('payment_methods');
            $table->foreignId('source_id')->constrained('payment_sources');
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
        Schema::dropIfExists('payment_urls');
    }
};
