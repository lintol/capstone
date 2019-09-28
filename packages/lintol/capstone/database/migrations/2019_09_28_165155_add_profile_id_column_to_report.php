<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddProfileIdColumnToReport extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->uuid('cached_profile_id')->nullable();
            $table->foreign('cached_profile_id')->references('id')->on('profiles')->onDelete('set null');

            $table->uuid('cached_data_package_id')->nullable();
            $table->foreign('cached_data_package_id')->references('id')->on('data_packages')->onDelete('set null');

            $table->uuid('cached_data_resource_id')->nullable();
            $table->foreign('cached_data_resource_id')->references('id')->on('data_resources')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropForeign('reports_cached_profile_id_foreign');
            $table->dropColumn('cached_profile_id');
        });
        Schema::table('reports', function (Blueprint $table) {
            $table->dropForeign('reports_cached_data_package_id_foreign');
            $table->dropColumn('cached_data_package_id');
        });
        Schema::table('reports', function (Blueprint $table) {
            $table->dropForeign('reports_cached_data_resource_id_foreign');
            $table->dropColumn('data_resource_id');
        });
    }
}
