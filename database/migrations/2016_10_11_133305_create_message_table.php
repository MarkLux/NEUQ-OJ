<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMessageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('message', function ($table) {
            $table->increments('id');;
            $table->enum('type' ,['text','image','video','audio']);
            $table->bigInteger('from_id');
            $table->bigInteger('to_id');
            $table->text('content');

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
