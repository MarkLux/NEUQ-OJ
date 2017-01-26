<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVerifyCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('verify_codes', function (Blueprint $table) {
            $table->bigInteger('user_id');
            $table->integer('type');
            $table->integer('device');
            $table->tinyInteger('via');
            $table->string('code',100);
            $table->bigInteger('created_at');
            $table->bigInteger('updated_at');
            $table->bigInteger('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('verify_codes');
    }
}
