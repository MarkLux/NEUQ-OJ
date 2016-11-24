<?php

use Illuminate\Database\Seeder;

class role_privilege_relationsTableSeeder extends DatabaseSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('role_privilege_relations')->insert(
            array(
                array(
                    'role_id'=>1,
                    'privilege_id'=>2,
                    'role'=>'teacher',
                    'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
                    'updated_at' => \Carbon\Carbon::now()->toDateTimeString()
                ),
                array(
                    'role_id'=>1,
                    'privilege_id'=>3,
                    'role'=>'teacher',
                    'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
                    'updated_at' => \Carbon\Carbon::now()->toDateTimeString()
                ),
                array(
                    'role_id'=>2,
                    'privilege_id'=>1,
                    'role'=>'admin',
                    'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
                    'updated_at' => \Carbon\Carbon::now()->toDateTimeString()
                ),
                array
                (
                    'role_id'=>2,
                    'privilege_id'=>2,
                    'role'=>'admin',
                    'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
                    'updated_at' => \Carbon\Carbon::now()->toDateTimeString()
                ),
                array(
                    'role_id'=>2,
                    'privilege_id'=>3,
                    'role'=>'admin',
                    'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
                    'updated_at' => \Carbon\Carbon::now()->toDateTimeString()
                ),
                array(
                    'role_id'=>2,
                    'privilege_id'=>4,
                    'role'=>'admin',
                    'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
                    'updated_at' => \Carbon\Carbon::now()->toDateTimeString()
                ),
                array(
                    'role_id'=>2,
                    'privilege_id'=>5,
                    'role'=>'admin',
                    'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
                    'updated_at' => \Carbon\Carbon::now()->toDateTimeString()
                )
            )
        );
    }

}
