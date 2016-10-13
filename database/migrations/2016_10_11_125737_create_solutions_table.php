<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSolutionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('solutions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('problem_id');
            $table->bigInteger('user_id');
            $table->integer('time');
            $table->integer('memory');
            $table->smallInteger('result');
            $table->integer('language');
            $table->string('ip',45);
            $table->bigInteger('contest_id');
            $table->integer('code_length');
            $table->timestamp('judge_time');
            $table->decimal('pass_rate',2,2);
            $table->integer('lint_error');
            $table->string('judger',45);
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
        Schema::drop('solutions');
    }
}
