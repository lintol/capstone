<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMetadataColumnToDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    /* public function up()
    {
        Schema::table('data', function (Blueprint $table) {
            $table->json('metadata')->default('{}');
        });
    } */

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('data', function (Blueprint $table) {
            $table->dropColumn('metadata');
        });
    }
}
