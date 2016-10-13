<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDiscussionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('discussion', function ($table) {
            $table->increments('id');;
            $table->bigInteger('problem_id');
            $table->bigInteger('user_id');
            $table->bigInteger('father');
            $table->text('content');
            $table->string('ip');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
