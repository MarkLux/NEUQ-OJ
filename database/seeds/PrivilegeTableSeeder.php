<?php

use Illuminate\Database\Seeder;

class PrivilegeTableSeeder extends DatabaseSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('privileges')->insert(
            array(
                array(
                    'name'=>'operate-user',
                    'description'=>'对用户的操作：admin(含）以上',
                    'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
                    'updated_at' => \Carbon\Carbon::now()->toDateTimeString(),
                ),
                array(
                    'name'=>'operate-group',
                    'description'=>'对用户组,竞赛，作业，考试的操作:teacher（含）之上',
                    'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
                    'updated_at' => \Carbon\Carbon::now()->toDateTimeString()
                ),
                array(
                    'name'=>'operate-problem',
                    'description'=>'对题目的操作：teacher（含）之上',
                    'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
                    'updated_at' => \Carbon\Carbon::now()->toDateTimeString()
                ),
                array(
                    'name'=>'operate-role',
                    'description'=>'对角色的操作：admin（含）以上',
                    'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
                    'updated_at' => \Carbon\Carbon::now()->toDateTimeString(),
                ),

            )
        );
    }
}
