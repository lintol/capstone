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
            $table->increments('id');
            $table->string('filename');
            $table->string('url');
            $table->string('filetype');
            $table->string('status')->default('new resource');
            $table->string('source');
            $table->string('reportid')->nullable($value = true);
            $table->string('user');
            $table->string('archived')->default(false);;
            $table->timestamps();
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
