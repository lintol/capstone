<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDataPackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('data_packages', function (Blueprint $table) {
            $table->uuid('id');

            $table->string('name')->default('[unnamed data package]');
            $table->json('metadata')->default('{}');

            $table->string('remote_id')->nullable();
            $table->string('url')->nullable();
            $table->string('source')->nullable();

            $table->uuid('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users');

            $table->string('archived')->default(false);;

            $table->timestamps();

            $table->unique('id');
        });

        Schema::table('data_resources', function (Blueprint $table) {
            $table->uuid('package_id')->nullable();
            $table->foreign('package_id')->references('id')->on('data_packages')->onDelete('set null');
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
            $table->dropForeign('data_resources_package_id_foreign');
        });
        Schema::dropIfExists('data_packages');
    }
}
