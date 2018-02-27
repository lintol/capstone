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
            $table->string('url')->nullable();
            $table->string('filetype')->nullable();
            $table->string('status')->default('new resource');
            $table->string('stored')->nullable();
            $table->string('reportid')->nullable($value = true);
            $table->string('user')->nullable();
            $table->string('archived')->default(false);;
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
