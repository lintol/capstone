<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateValidationRunsForeignKeys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            Schema::table('validation_runs', function (Blueprint $table) {
                $table->dropForeign('validations_configuration_id_foreign');
            });
        } catch (\Throwable $e) {
            DB::rollback();
            Schema::table('validation_runs', function (Blueprint $table) {
                $table->dropForeign('validation_runs_configuration_id_foreign');
            });
        }

        Schema::table('validation_runs', function (Blueprint $table) {
            $table->dropColumn('configuration_id');
            $table->dropColumn('metadata');
            $table->json('doorstep_definition')->default('{}');
            $table->json('settings')->default('{}');
            $table->uuid('profile_id')->nullable();
            $table->foreign('profile_id')->references('id')->on('profiles')->onDelete('set null');
            $table->uuid('creator_id')->nullable();
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
        });

        Schema::table('reports', function (Blueprint $table) {
            $table->dropForeign('reports_validation_id_foreign');
            $table->dropColumn('validation_id');
            $table->uuid('run_id')->nullable();
            $table->foreign('run_id')->references('id')->on('validation_runs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('validation_runs', function (Blueprint $table) {
            $table->dropForeign('validation_runs_profile_id_foreign');
            $table->dropColumn('profile_id');
            $table->uuid('processor_id')->nullable();
            $table->foreign('processor_id')->references('id')->on('processors')->onDelete('cascade');

            $table->json('metadata')->default('{}');
            $table->dropColumn('doorstep_definition');
            $table->dropColumn('settings');
            $table->uuid('configuration_id')->nullable();
            $table->foreign('configuration_id')->references('id')->on('validation_runs')->onDelete('cascade');
        });

        Schema::table('reports', function (Blueprint $table) {
            $table->dropForeign('reports_run_id_foreign');
            $table->dropColumn('run_id');
            $table->uuid('validation_id')->nullable();
            $table->foreign('validation_id')->references('id')->on('validation_runs')->onDelete('cascade');
        });
    }
}
