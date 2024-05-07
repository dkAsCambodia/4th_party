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
        Schema::create('gateway_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account_id')->nullable();
            $table->string('description')->nullable();
            $table->string('e_comm_website')->nullable();
            $table->string('gateway')->nullable();
            $table->string('website_description')->nullable();
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
        Schema::dropIfExists('gateway_accounts');
    }
};
