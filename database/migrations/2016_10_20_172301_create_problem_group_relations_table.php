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
            $table->bigInteger('problem_group_id');
            $table->bigInteger('problem_id');
            $table->primary(['problem_group_id','problem_id']);
            $table->integer('problem_score');
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
