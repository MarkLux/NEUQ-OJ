<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProblemGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('problem_groups', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title',100);
            $table->bigInteger('user_group_id');
            $table->integer('type');
            $table->string('description',512);
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->tinyInteger('private');
            $table->tinyInteger('status');
            $table->string('langmask',100);
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
        Schema::drop('problem_groups');
    }
}
