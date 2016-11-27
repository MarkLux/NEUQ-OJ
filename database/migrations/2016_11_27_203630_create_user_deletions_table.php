<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserOperationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_deletions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('gid');
            $table->date('time');
            $table->string('table_name',100);
            $table->bigIncrements('user_id');
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
        Schema::drop('user_deletions');
    }
}
