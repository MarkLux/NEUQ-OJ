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
            $table->integer('submit');
            $table->integer('solved');
            $table->string('password',45);
            $table->dateTime('reg_time');
            $table->string('name',100);
            $table->string('school',100);
            $table->bigInteger('code_length');
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
