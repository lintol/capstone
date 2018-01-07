<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProcessorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('processors', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name');

            $table->uuid('creator_id')->nullable();
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');

            $table->text('description');
            $table->text('unique_tag');

            $table->string('module');
            $table->text('content');

            $table->timestamps();

            $table->unique('id');
            $table->unique('unique_tag');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('processors');
    }
}
