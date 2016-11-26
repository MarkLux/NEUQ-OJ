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
        Schema::create('problem_category_relations', function (Blueprint $table) {
            $table->bigInteger('problem_id');
            $table->integer('category_id');
            $table->string('problem_title',100);
            $table->string('category_title',45);
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
        Schema::drop('problem_category_relations');
    }
}
