  <?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProblemGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('problem_groups', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title',100);
            $table->bigInteger('user_group_id')->nullable();
            $table->integer('type');
            $table->text('description')->nullable();
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->bigInteger('creator_id');
            $table->string('creator_name',100);
            $table->integer('private');
            $table->tinyInteger('status');
            $table->string('password')->nullable();
            $table->string('langmask',100)->nullable();
            $table->integer('problem_count')->default(0);//记录题目数量
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
        Schema::drop('problem_groups');
    }
}
