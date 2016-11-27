<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserGroupRelationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_group_relations', function (Blueprint $table) {
            $table->bigInteger('group_id');
            $table->bigInteger('user_id');
            $table->string('user_name',100);
            $table->string('user_code',45);
            $table->string('user_tag',100);
            $table->timestamps();
            $table->primary(['group_id', 'user_id']);
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
        Schema::drop('user_group_relations');
    }
}
