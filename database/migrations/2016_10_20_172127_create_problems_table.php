<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProblemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('problems', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->string('title',100);
            $table->text('description');
            $table->integer('difficulty');
            $table->text('input');
            $table->text('output');
            $table->text('sample_input');
            $table->text('sample_output');
            $table->string('spj',1);
            $table->text('hint');
            $table->string('source',100);
            $table->integer('time_limit');
            $table->integer('memory');
            $table->integer('submit');
            $table->integer('solved');
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
        Schema::drop('problems');
    }
}
