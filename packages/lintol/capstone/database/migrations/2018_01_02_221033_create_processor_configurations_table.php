<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProcessorConfigurationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('validations', function (Blueprint $table) {
            $table->dropForeign('validations_processor_id_foreign');
            $table->dropColumn('processor_id');
        });

        Schema::create('processor_configurations', function (Blueprint $table) {
            $table->uuid('id');

            $table->uuid('processor_id')->nullable();
            $table->foreign('processor_id')->references('id')->on('processors')->onDelete('cascade');

            $table->uuid('profile_id')->nullable();
            $table->foreign('profile_id')->references('id')->on('profiles')->onDelete('cascade');

            $table->json('configuration')->default('{}');
            $table->json('metadata')->default('{}');
            $table->json('rules')->default('{}');

            $table->timestamps();
            $table->unique('id');
        });

        Schema::table('validations', function (Blueprint $table) {
            $table->uuid('configuration_id')->nullable();
            $table->foreign('configuration_id')->references('id')->on('processor_configurations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('validations', function (Blueprint $table) {
            $table->dropForeign('validations_configuration_id_foreign');
            $table->dropColumn('configuration_id');
        });

        Schema::drop('processor_configurations');

        Schema::table('validations', function (Blueprint $table) {
            $table->uuid('processor_id')->nullable();
            $table->foreign('processor_id')->references('id')->on('processors')->onDelete('cascade');
        });
    }
}
