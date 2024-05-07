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
        Schema::create('api_documents', function (Blueprint $table) {
            $table->id();

            $table->string('api_doc_file')->nullable();

            $table->string('locale')->nullable();

            $table->string('product_id_examp')->nullable();
            $table->string('product_id_desc')->nullable();
            $table->string('merchant_code_examp')->nullable();
            $table->string('merchant_code_desc')->nullable();
            $table->string('customer_name_examp')->nullable();
            $table->string('customer_name_desc')->nullable();
            $table->string('customer_id_examp')->nullable();
            $table->string('customer_id_desc')->nullable();
            $table->string('transaction_id_examp')->nullable();
            $table->string('transaction_id_desc')->nullable();
            $table->string('call_back_url_examp')->nullable();
            $table->string('call_back_url_desc')->nullable();

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
        Schema::dropIfExists('api_documents');
    }
};
