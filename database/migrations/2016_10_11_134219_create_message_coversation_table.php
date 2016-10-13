<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMessageCoversationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('message_conversation', function ($table) {
            $table->increments('id');;
            $table->bigInteger('from_id');
            $table->bigInteger('to_id');
            $table->integer('message_num');
            $table->integer('latest_message_user_id');
            $table->integer('latest_message_time');
            $table->text('latest_message_content');
            $table->enum('type' ,['text','image','video','audio']);
            $table->integer('unread_num');

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
