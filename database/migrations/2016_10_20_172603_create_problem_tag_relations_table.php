<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProblemTagRelationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('problem_tag_relations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('problem_id');
            $table->integer('tag_id');
            $table->string('problem_title',100);
            $table->string('tag_title',45);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('problem_tag_relations');
    }
}
