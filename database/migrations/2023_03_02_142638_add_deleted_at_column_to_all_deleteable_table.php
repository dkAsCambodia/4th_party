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
        Schema::table('users', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('payment_channels', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('payment_methods', function (Blueprint $table) {
            $table->softDeletes();
        });

        /* Schema::table('payment_sources', function (Blueprint $table) {
            $table->softDeletes();
        }); */

        Schema::table('payment_urls', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('payment_accounts', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('payment_maps', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('agents', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('merchants', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('whitelist_ips', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('payment_channels', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('payment_methods', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('payment_sources', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('payment_urls', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('payment_accounts', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('payment_maps', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('agents', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('merchants', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('whitelist_ips', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });
    }
};
