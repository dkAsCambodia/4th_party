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
        Schema::table('payment_details', function (Blueprint $table) {

            $table->longText('request_data')->nullable();
            $table->longText('response_data')->nullable();
            $table->string('payment_status')->nullable()->default('pending')->change();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payment_details', function (Blueprint $table) {

            $table->dropColumn('request_data');
            $table->dropColumn('response_data');
            $table->integer('payment_status')->nullable()->default(null)->change();

        });
    }
};
