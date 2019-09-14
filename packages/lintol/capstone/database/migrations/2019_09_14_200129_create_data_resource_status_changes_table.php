<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDataResourceStatusChangesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('data_resource_status_changes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('data_resource_id');
            $table->foreign('data_resource_id')->references('id')->on('data_resources')->onDelete('cascade');
            $table->string('new_status');
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
        Schema::dropIfExists('data_resource_status_changes');
    }
}
