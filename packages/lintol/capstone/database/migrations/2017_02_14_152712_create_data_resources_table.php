<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDataResourcesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('data_resources', function (Blueprint $table) {
            $table->uuid('id');

            $table->string('name')->default('[unnamed data resource]');
            $table->json('settings')->default('{}');
            $table->text('content')->nullable();

            $table->string('filename')->nullable();
            $table->string('remote_id')->nullable();
            $table->string('url')->nullable();
            $table->string('filetype')->nullable();
            $table->string('status')->default('new resource');
            $table->string('source')->nullable();
            $table->string('reportid')->nullable($value = true);

            $table->uuid('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users');

            $table->string('archived')->default(false);;

            $table->string('resourceable_type')->nullable();
            $table->uuid('resourceable_id')->nullable();

            $table->timestamps();

            $table->unique('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('data_resources');
    }
}
