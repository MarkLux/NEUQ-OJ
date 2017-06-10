<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserGroupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('owner_id');
            $table->tinyInteger('privacy'); // 加密方式
//            $table->string('owner_name',100);  // 不符合范式，会引发更新问题
            $table->boolean('is_closed')->default(0);
            $table->string('name',100);
            $table->string('description',512);
            $table->integer('max_size');
            $table->string('password',255)->nullable()->default(null);
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
        Schema::drop('user_groups');
    }
}
