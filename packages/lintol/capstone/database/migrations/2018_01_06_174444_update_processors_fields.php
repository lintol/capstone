<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateProcessorsFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('processors', function (Blueprint $table) {
            $table->json('rules')->default('{}');
            $table->json('definition')->default('{}');
            $table->json('configuration_options')->default('{}');
            $table->json('configuration_defaults')->default('{}');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('processors', function (Blueprint $table) {
            $table->dropColumn('rules');
            $table->dropColumn('definition');
            $table->dropColumn('configuration_options');
            $table->dropColumn('configuration_defaults');
        });
    }
}
