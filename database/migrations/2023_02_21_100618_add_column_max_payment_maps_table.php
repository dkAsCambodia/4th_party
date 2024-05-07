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
        //
        Schema::table('payment_maps', function (Blueprint $table) {
             $table->decimal('min_value', 8,2)->after('map_value');
         });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('payment_maps', function (Blueprint $table) {
            $table->dropColumn('min_value', 8,2);
        });
    }
};
