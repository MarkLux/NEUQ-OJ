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
            $table->bigIncrements('id');
            $table->string('title',100);
            $table->text('description');
            $table->integer('difficulty');
//            $table->text('input');
//            $table->text('output');
            $table->text('sample_input')->nullable();
            $table->text('sample_output');
            $table->string('spj',1);
            $table->text('hint')->nullable();
            $table->string('source',100)->nullable();
            $table->integer('time_limit');
            $table->integer('memory_limit');
            $table->integer('accepted')->nullable();
            $table->integer('submit')->nullable();
            $table->boolean('is_public');
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
