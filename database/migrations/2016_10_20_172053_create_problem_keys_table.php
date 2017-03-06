<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProblemKeysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('problem_keys', function (Blueprint $table) {
            $table->bigInteger('problem_id')->primary();
            $table->string('title',100);
            $table->text('key');
            $table->bigInteger('author_id');
            $table->string('author_name');
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
        Schema::drop('problem_keys');
    }
}
