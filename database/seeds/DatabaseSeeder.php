<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(rolesTableSeeder::class);
        $this->call(user_role_relationsTableSeeder::class);
    }
}
