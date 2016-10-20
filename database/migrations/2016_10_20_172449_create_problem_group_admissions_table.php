<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProblemGroupAdmissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('problem_group_admissions', function (Blueprint $table) {
            $table->bigInteger('problem_group_id');
            $table->bigInteger('user_group_id');
            $table->primary(['problem_group_id','user_group_id']);
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
        Schema::drop('problem_group_admissions');
    }
}
