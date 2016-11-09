<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePgUgRelationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pg_ug_relations', function (Blueprint $table) {
            $table->bigInteger('pg_id');
            $table->bigInteger('ug_id');
            $table->integer('pg_type');
            $table->string('pg_title',100);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('pg_ug_relations');
    }
}
