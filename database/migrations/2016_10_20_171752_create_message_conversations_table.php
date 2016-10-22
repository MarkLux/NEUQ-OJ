<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMessageConversationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('message_conversations', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('from_id');
            $table->bigInteger('told');
            $table->integer('message_num');
            $table->integer('latest_message_user_id');
            $table->integer('latest_message_time');
            $table->text('latest_message_content');
            $table->enum('choices',['text','image','vedio','audio']);
            $table->integer('unread_num');
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
        Schema::drop('message_conversations');
    }
}
