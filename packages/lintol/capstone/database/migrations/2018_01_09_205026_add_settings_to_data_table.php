<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSettingsToDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('data', function (Blueprint $table) {
            $table->string('name')->default('[unnamed data resource]');
            $table->string('source_uri')->nullable();
            $table->json('settings')->default('{}');
            $table->string('format')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('data', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->dropColumn('settings');
            $table->dropColumn('source_uri');
            $table->dropColumn('format');
        });
    }
}
