<?php

use Illuminate\Database\Seeder;

class user_privilege_relationsTableSeeder extends DatabaseSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('user_privilege_relations')->insert(
            array(
                array(
                  'user_id'=>1,
                    'privilege_id'=>1,
                    'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
                    'updated_at' => \Carbon\Carbon::now()->toDateTimeString()
                ),
                array(
                    'user_id'=>1,
                    'privilege_id'=>2,
                    'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
                    'updated_at' => \Carbon\Carbon::now()->toDateTimeString()
                ),
                array(
                    'user_id'=>1,
                    'privilege_id'=>3,
                    'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
                    'updated_at' => \Carbon\Carbon::now()->toDateTimeString()
                ),
                array(
                    'user_id'=>1,
                    'privilege_id'=>4,
                    'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
                    'updated_at' => \Carbon\Carbon::now()->toDateTimeString()
                ),

                array(
                    'user_id'=>2,
                    'privilege_id'=>1,
                    'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
                    'updated_at' => \Carbon\Carbon::now()->toDateTimeString()
                ),
                array(
                    'user_id'=>2,
                    'privilege_id'=>2,
                    'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
                    'updated_at' => \Carbon\Carbon::now()->toDateTimeString()
                ),
                array(
                    'user_id'=>2,
                    'privilege_id'=>3,
                    'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
                    'updated_at' => \Carbon\Carbon::now()->toDateTimeString()
                ),
                array(
                    'user_id'=>2,
                    'privilege_id'=>4,
                    'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
                    'updated_at' => \Carbon\Carbon::now()->toDateTimeString()
                ),

                array(
                    'user_id'=>2,
                    'privilege_id'=>5,
                    'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
                    'updated_at' => \Carbon\Carbon::now()->toDateTimeString()
                ),
                array(
                    'user_id'=>3,
                    'privilege_id'=>2,
                    'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
                    'updated_at' => \Carbon\Carbon::now()->toDateTimeString()
                ),
                array(
                    'user_id'=>3,
                    'privilege_id'=>3,
                    'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
                    'updated_at' => \Carbon\Carbon::now()->toDateTimeString()
                ),

            )
        );
    }
}
