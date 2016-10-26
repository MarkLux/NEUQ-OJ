<?php

use Illuminate\Database\Seeder;

class user_role_relationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('user_role_relations')->insert(
          array(
              array(
              'user_id'=>1,
              'role_id'=>2,
              'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
              'updated_at' => \Carbon\Carbon::now()->toDateTimeString()
          ),
              array(
                  'user_id'=>2,
                  'role_id'=>2,
                  'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
                  'updated_at' => \Carbon\Carbon::now()->toDateTimeString()
              ),
              array(
                'user_id'=>3,
                  'role_id'=>1,
                  'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
                  'updated_at' => \Carbon\Carbon::now()->toDateTimeString()
              )
          )
        );
    }
}
