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
            $table->bigInteger('id')->primary();//id其实是在source_code表生成放进来的
            $table->bigInteger('problem_id');
            $table->bigInteger('user_id');
            $table->Integer('time')->nullable();
            $table->Integer('memory')->nullable();
            $table->smallInteger('result')->default('0');
            $table->integer('language');
            $table->string('ip', 45)->nullable();
            $table->bigInteger('problem_group_id')->nullable();
            $table->integer('problem_num')->nullable()->default('-1');
            $table->integer('code_length');
            $table->timestamp('judge_time')->nullable();
            $table->decimal('pass_rate')->nullable();
            $table->string('judger', 45)->nullable();
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
