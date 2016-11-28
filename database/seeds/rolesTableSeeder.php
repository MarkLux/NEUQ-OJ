<?php

use Illuminate\Database\Seeder;

class rolesTableSeeder extends DatabaseSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('roles')->insert(
            array(
                array(
                    'name'=>'teacher',
                    'description'=>'教师角色',
                    'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
                    'updated_at' => \Carbon\Carbon::now()->toDateTimeString()
                ),
                array
                (
                    'name'=>'admin',
                    'description'=>'（超级）管理员',
                    'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
                    'updated_at' => \Carbon\Carbon::now()->toDateTimeString()
                )

            )
        );
    }
}
