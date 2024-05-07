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
        Schema::create('parameter_values', function (Blueprint $table) {
            $table->id();

            $table->foreignId('parameter_setting_id')->constrained('parameter_settings');
            $table->foreignId('gateway_account_method_id')->constrained('gateway_account_methods');
            $table->string('gateway_channet_id')->nullable();
            $table->string('parameter_setting_value')->nullable();
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
        Schema::dropIfExists('parameter_values');
    }
};
