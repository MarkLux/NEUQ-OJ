<?php

use Illuminate\Database\Seeder;

class PrivilegeTableSeeder extends Seeder
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
                    'description'=>'对用户的操作：admin(含）以上'
                ),
                array(
                    'name'=>'operate-group',
                    'description'=>'对用户组,竞赛，作业，考试的操作:teacher（含）之上'
                ),
                array(
                    'name'=>'operate-problem',
                    'description'=>'对题目的操作：teacher（含）之上'
                ),
                array(
                    'name'=>'operate-teacher-apply',
                    'description'=>'对教师申请的操作：admin（含）以上'
                ),
                array(
                    'name'=>'operate-admin-apply',
                    'description'=>'对管理员申请的操作：superAdmin'
                )
            )
        );
    }
}
