<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateProcessorConfigurationsFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('processor_configurations', function (Blueprint $table) {
            $table->dropColumn('metadata');
            $table->json('user_configuration_storage')->default('{}');
            $table->json('definition')->default('{}');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('processor_configurations', function (Blueprint $table) {
            $table->json('metadata')->default('{}');
            $table->dropColumn('user_configuration_storage');
            $table->dropColumn('definition');
        });
    }
}
