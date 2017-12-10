<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name');

            $table->uuid('creator_id')->nullable();
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('cascade');

            $table->text('description');
            $table->text('version');
            $table->text('unique_tag');
            $table->unique('id');

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
        Schema::dropIfExists('profiles');
    }
}
