<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLocaleColumnToDataResourcesAndDataPackagesTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('data_resources', function (Blueprint $table) {
            $table->string('locale')->nullable();
        });

        Schema::table('data_packages', function (Blueprint $table) {
            $table->string('locale')->nullable();
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
            $table->dropColumn('locale');
        });

        Schema::table('data_packages', function (Blueprint $table) {
            $table->dropColumn('locale');
        });
    }
}
