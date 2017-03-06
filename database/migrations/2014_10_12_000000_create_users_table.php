<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('email',100);
            $table->string('mobile',45);
            $table->string('login_name',100)->nullable()->unique();
            $table->integer('submit')->default('0');
            $table->integer('solved')->default('0');
            $table->string('password',255);
            $table->string('name',100);
            $table->string('school',100);
            $table->bigInteger('code_length');
            $table->string('signature',512)->nullable();
            $table->tinyInteger('status');
            $table->tinyInteger('role')->nullable();
            $table->rememberToken();
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
        Schema::drop('users');
    }
}
