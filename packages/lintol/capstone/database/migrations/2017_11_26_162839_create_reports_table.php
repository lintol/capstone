<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->uuid('id');
            $table->text('name');

            $table->uuid('owner_id')->nullable();
            $table->foreign('owner_id')->references('id')->on('users')->onDelete('set null');

            $table->uuid('validation_id')->nullable();
            $table->foreign('validation_id')->references('id')->on('validations')->onDelete('set null');

            $table->json('content');

            $table->text('profile')->default('');
            $table->integer('errors')->default(0);
            $table->integer('warnings')->default(0);
            $table->integer('passes')->default(0);
            $table->integer('quality_score')->default(100);

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
        Schema::dropIfExists('reports');
    }
}
