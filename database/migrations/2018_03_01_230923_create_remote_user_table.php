<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRemoteUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('remote_users', function (Blueprint $table) {
            $table->uuid('id');

            $table->uuid('user_id');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->string('driver');
            $table->string('driver_server');
            $table->string('remote_id_hash')->nullable();
            $table->string('remote_email_hash')->nullable();
            $table->string('remote_token')->nullable();

            $table->string('resourceable_type')->nullable();
            $table->string('resourceable_id')->nullable();

            $table->timestamps();
            $table->primary('id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->uuid('primary_remote_user_id')->nullable();
            $table->foreign('primary_remote_user_id')
                ->references('id')
                ->on('remote_users')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('primary_remote_user_id');
        });
        Schema::drop('remote_users');
    }
}
