<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSourceColumnToJson extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('data_packages', function (Blueprint $table) {
            $table->dropColumn('source');
        });
        Schema::table('data_packages', function (Blueprint $table) {
            $table->json('source')->nullable();
            $table->uuid('ckan_instance_id')->nullable();
        });
        Schema::table('data_resources', function (Blueprint $table) {
            $table->dropColumn('source');
        });
        Schema::table('data_resources', function (Blueprint $table) {
            $table->json('source')->nullable();
            $table->uuid('ckan_instance_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('data_resources', function (Blueprint $table) {
            $table->dropColumn('source');
            $table->dropColumn('ckan_instance_id')->nullable();
        });
        Schema::table('data_resources', function (Blueprint $table) {
            $table->string('source')->nullable();
            $table->dropColumn('ckan_instance_id')->nullable();
        });
        Schema::table('data_packages', function (Blueprint $table) {
            $table->dropColumn('source');
        });
        Schema::table('data_packages', function (Blueprint $table) {
            $table->string('source')->nullable();
        });
    }
}
