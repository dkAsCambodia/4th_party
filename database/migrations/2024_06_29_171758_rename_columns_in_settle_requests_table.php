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
        Schema::table('settle_requests', function (Blueprint $table) {
            $table->renameColumn('commission', 'mdr_fee_amount');
            $table->renameColumn('sub_total', 'net_amount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('settle_requests', function (Blueprint $table) {
            $table->renameColumn('mdr_fee_amount', 'commission');
            $table->renameColumn('net_amount', 'sub_total');
        });
    }
};
