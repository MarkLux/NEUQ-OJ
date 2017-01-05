<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProblemGroupRelationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('problem_group_relations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('problem_group_id');
            $table->bigInteger('problem_id');
            $table->string('problem_title',100);
            $table->integer('problem_score')->nullable();
            $table->integer('problem_num');
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
        Schema::drop('problem_group_relations');
    }
}
