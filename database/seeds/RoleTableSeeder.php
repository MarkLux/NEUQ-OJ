<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $current = new \Carbon\Carbon();

        /**
         * 创建系统基本角色
         */

        /**
         * 授权
         */


        /**
         * 管理员直接提取所有权限,同时重置索引格式
         */

        $privileges = DB::table('privileges')->get(['name']);

        $adminRelations = [];

        foreach ($privileges as $privilege) {
            $adminRelations[] = [
                'role_name' => 'admin',
                'privilege_name' => $privilege->name
            ];
        }

        DB::transaction(function ()use($adminRelations,$current){

            // 创建两个角色

            DB::table('roles')->insert([
                'name' => 'admin',
                'display_name' => '系统管理员',
                'description' => '系统的最高管理员，拥有系统所有权限',
                'created_at' => $current,
                'updated_at' => $current
            ]);

            DB::table('roles')->insert([
                'name' => 'teacher',
                'display_name' => '普通教师',
                'description' => '普通的教师用户，可以创建题目，竞赛和用户组',
                'created_at' => $current,
                'updated_at' => $current
            ]);

            DB::table('role_privilege_relations')->insert($adminRelations);

            DB::table('role_privilege_relations')->insert([

                /**
                 * 教师部分
                 */
                [
                    'role_name' => 'teacher',
                    'privilege_name' => 'create-problem'
                ],
                [
                    'role_name' => 'teacher',
                    'privilege_name' => 'create-problem-tag'
                ],
                [
                    'role_name' => 'teacher',
                    'privilege_name' => 'update-problem-tag'
                ],
                [
                    'role_name' => 'teacher',
                    'privilege_name' => 'delete-problem-tag'
                ],
                [
                    'role_name' => 'teacher',
                    'privilege_name' => 'give-problem-tag'
                ],
                [
                    'role_name' => 'teacher',
                    'privilege_name' => 'remove-problem-tag'
                ],
                [
                    'role_name' => 'teacher',
                    'privilege_name' => 'create-contest'
                ],
                [
                    'role_name' => 'teacher',
                    'privilege_name' => 'create-user-group'
                ]
            ]);
        });

    }
}
