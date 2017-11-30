<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateValidationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('validations', function (Blueprint $table) {
            $table->uuid('id');
            $table->integer('completion_status')->nullable();

            $table->uuid('data_id')->nullable();
            $table->foreign('data_id')->references('id')->on('data')->onDelete('cascade');

            $table->uuid('processor_id')->nullable();
            $table->foreign('processor_id')->references('id')->on('processors')->onDelete('cascade');

            $table->string('doorstep_server_id')->nullable();
            $table->string('doorstep_session_id')->nullable();

            $table->json('report')->nullable();
            $table->text('output')->nullable();

            $table->timestamp('requested_at')->nullable();
            $table->timestamp('initiated_at')->nullable();
            $table->timestamp('completed_at')->nullable();

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
        Schema::dropIfExists('validations');
    }
}
