<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCkanInstancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ckan_instances', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name');
            $table->string('uri');
            $table->timestamps();
            $table->unique('id');

            $table->text('client_id')->nullable();
            $table->text('client_secret')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ckan_instances');
    }
}
