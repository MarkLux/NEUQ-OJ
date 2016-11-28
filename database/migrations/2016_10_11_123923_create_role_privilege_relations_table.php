<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRolePrivilegeRelationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('role_privilege_relations', function (Blueprint $table) {
            $table->integer('role_id');
            $table->integer('privilege_id');
            $table->primary(['role_id','privilege_id']);
            $table->string('role',50);
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
        Schema::drop('role_privilege_relations');
    }
}
