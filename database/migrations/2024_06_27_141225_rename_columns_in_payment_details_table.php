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
            $table->renameColumn('exchangeRate', 'mdr_fee_amount');
            $table->renameColumn('merchantAmount', 'net_amount');
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
            $table->renameColumn('mdr_fee_amount', 'exchangeRate');
            $table->renameColumn('net_amount', 'merchantAmount');
        });
    }
};
