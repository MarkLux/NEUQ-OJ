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
            $table->bigInteger('creator_id');
            $table->string('creator_name',100);
//            $table->text('input');
//            $table->text('output');
            $table->text('sample_input')->nullable();
            $table->text('sample_output');
            $table->text('input')->nullable();
            $table->text('output')->nullable();
            $table->string('spj',1);
            $table->text('hint')->nullable();
            $table->string('source',100)->nullable();
            $table->integer('time_limit');
            $table->integer('memory_limit');
            $table->integer('accepted')->default(0);
            $table->integer('submit')->default(0);
            $table->boolean('is_public');
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
        Schema::drop('problems');
    }
}
