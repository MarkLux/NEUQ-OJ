<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PrivilegeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $current = new \Carbon\Carbon();

        DB::table('privileges')->insert([

            /**
             * 题目部分
             */

            [
                'name' => 'view-all-problems',
                'display_name' => '查看所有题目',
                'description' => '查看所有的题目，无私有性限制',
                'created_at' => $current,
                'updated_at' => $current
            ],
            [
                'name' => 'create-problem',
                'display_name' => '创建题目',
                'description' => '创建一个新的题目',
                'created_at' => $current,
                'updated_at' => $current
            ],
            [
                'name' => 'update-any-problem',
                'display_name' => '修改所有题目',
                'description' => '更新任意一个题目的信息',
                'created_at' => $current,
                'updated_at' => $current
            ],
            [
                'name' => 'delete-any-problem',
                'display_name' => '删除任意题目',
                'description' => '删除任意一个题目',
                'created_at' => $current,
                'updated_at' => $current
            ],
            [
                'name' => 'access-any-problem',
                'display_name' => '获取任意题目',
                'description' => '获取任意一个题目的访问权',
                'created_at' => $current,
                'updated_at' => $current
            ],
            [
                'name' => 'get-run-data',
                'display_name' => '获取测试数据',
                'description' => '获取题目的测试数据',
                'created_at' => $current,
                'updated_at' => $current
            ],

            /**
             * 题目分类
             */

            [
                'name' => 'create-problem-tag',
                'display_name' => '创建题目tag',
                'description' => '添加一个新的题目tag',
                'created_at' => $current,
                'updated_at' => $current
            ],
            [
                'name' => 'update-problem-tag',
                'display_name' => '更新题目tag',
                'description' => '更新一个题目tag的内容',
                'created_at' => $current,
                'updated_at' => $current
            ],
            [
                'name' => 'delete-problem-tag',
                'display_name' => '删除题目tag',
                'description' => '删除一个题目tag',
                'created_at' => $current,
                'updated_at' => $current
            ],
            [
                'name' => 'give-problem-tag',
                'display_name' => '给题目贴tag',
                'description' => '为题目添加tag',
                'created_at' => $current,
                'updated_at' => $current
            ],
            [
                'name' => 'remove-problem-tag',
                'display_name' => '移除题目的tag',
                'description' => '移除题目的tag',
                'created_at' => $current,
                'updated_at' => $current
            ],
            [
                'name' => 'import-problems',
                'display_name' => '导入题目',
                'description' => '通过fps文件导入题目',
                'created_at' => $current,
                'updated_at' => $current
            ],
            [
                'name' => 'export-problems',
                'display_name' => '导出题目',
                'description' => '以fps的形式导出题目',
                'created_at' => $current,
                'updated_at' => $current
            ],
            /**
             * 竞赛部分
             */

            [
                'name' => 'view-all-contest',
                'display_name' => '查看所有竞赛',
                'description' => '查看所有的竞赛',
                'created_at' => $current,
                'updated_at' => $current
            ],
            [
                'name' => 'create-contest',
                'display_name' => '创建竞赛',
                'description' => '创建新竞赛',
                'created_at' => $current,
                'updated_at' => $current
            ],
            [
                'name' => 'update-any-contest',
                'display_name' => '修改任意竞赛',
                'description' => '更新任意一个竞赛的信息',
                'created_at' => $current,
                'updated_at' => $current
            ],
            [
                'name' => 'delete-any-contest',
                'display_name' => '删除任意竞赛',
                'description' => '删除任意一个竞赛',
                'created_at' => $current,
                'updated_at' => $current
            ],
            [
                'name' => 'access-any-contest',
                'display_name' => '进入任意竞赛',
                'description' => '获取任意一个竞赛的参与权限',
                'created_at' => $current,
                'updated_at' => $current
            ],

            /**
             * 用户组部分
             */

            [
                'name' => 'create-user-group',
                'display_name' => '创建用户组',
                'description' => '创建一个用户组',
                'created_at' => $current,
                'updated_at' => $current
            ],
            [
                'name' => 'update-any-user-group',
                'display_name' => '修改所有用户组',
                'description' => '更新任意一个用户组的基本信息',
                'created_at' => $current,
                'updated_at' => $current
            ],
            [
                'name' => 'delete-any-user-group',
                'display_name' => '删除任意用户组',
                'description' => '删除任意一个用户组',
                'created_at' => $current,
                'updated_at' => $current
            ],
            [
                'name' => 'add-homework',
                'display_name' => '任意添加作业',
                'description' => '为任意一个用户组添加一个作业',
                'created_at' => $current,
                'updated_at' => $current
            ],

            /**
             * 作业部分
             */

            [
                'name' => 'update-any-homework',
                'display_name' => '修改任意作业',
                'description' => '更新任意一个作业',
                'created_at' => $current,
                'updated_at' => $current
            ],
            [
                'name' => 'delete-any-homework',
                'display_name' => '删除任意作业',
                'description' => '删除任意一个作业',
                'created_at' => $current,
                'updated_at' => $current
            ],
            [
                'name' => 'access-any-homework',
                'display_name' => '进入任意作业',
                'description' => '获取任意一个作业的权限',
                'created_at' => $current,
                'updated_at' => $current
            ],

            /**
             * 系统管理部分
             */

            [
                'name' => 'generate-users',
                'display_name' => '批量生成用户',
                'description' => '批量生成用户',
                'created_at' => $current,
                'updated_at' => $current
            ],
            [
                'name' => 'lock-user',
                'display_name' => '锁定用户',
                'description' => '锁定一个用户，被锁定后的用户将被撤销所有权限',
                'created_at' => $current,
                'updated_at' => $current
            ],
            [
                'name' => 'unlock-user',
                'display_name' => '解锁用户',
                'description' => '解锁一个被锁定的用户',
                'created_at' => $current,
                'updated_at' => $current
            ],

            /**
             * 新闻公告
             */

            [
                'name' => 'add-news',
                'display_name' => '添加通知',
                'description' => '添加首页的新闻或者公告',
                'created_at' => $current,
                'updated_at' => $current
            ],
            [
                'name' => 'update-news',
                'display_name' => '修改通知',
                'description' => '修改首页的新闻或者公告',
                'created_at' => $current,
                'updated_at' => $current
            ],
            [
                'name' => 'delete-news',
                'display_name' => '删除通知',
                'description' => '删除首页的新闻或者公告',
                'created_at' => $current,
                'updated_at' => $current
            ],

            /**
             * 权限部分
             */
            [
                'name' => 'operate-role',
                'display_name' => '管理角色',
                'description' => '对角色进行增删改的操作',
                'created_at' => $current,
                'updated_at' => $current
            ],
            [
                'name' => 'give-role',
                'display_name' => '授予角色',
                'description' => '授予用户角色',
                'created_at' => $current,
                'updated_at' => $current
            ],
            [
                'name' => 'update-role',
                'display_name' => '修改角色',
                'description' => '更新一个用户的角色',
                'created_at' => $current,
                'updated_at' => $current
            ],
            [
                'name' => 'remove-role',
                'display_name' => '移除角色',
                'description' => '移除一个用户身上的角色',
                'created_at' => $current,
                'updated_at' => $current
            ],

            /**
             * 判题
             */

            [
                'name' => 'access-any-source-code',
                'display_name' => '获取源代码',
                'description' => '获取任意一次提交的源代码',
                'created_at' => $current,
                'updated_at' => $current
            ],
            /*
             * 题解
             */
            [
                'name' => 'create-problem-key',
                'display_name' => '创建题解',
                'description' => '对题目创建题解',
                'created_at' => $current,
                'updated_at' => $current
            ],
            [
                'name' => 'update-problem-key',
                'display_name' => '更改题解',
                'description' => '修改题目的题解',
                'created_at' => $current,
                'updated_at' => $current
            ],
            [
                'name' => 'delete-problem-key',
                'display_name' => '删除题解',
                'description' => '删除题目的题解',
                'created_at' => $current,
                'updated_at' => $current
            ],
            [
                'name' => 'access-any-key',
                'display_name' => '获取题解',
                'description' => '能查看所有题解',
                'created_at' => $current,
                'updated_at' => $current
            ]
        ]);
    }
}
