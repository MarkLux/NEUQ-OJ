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
        $this->call(PrivilegeTableSeeder::class);
        $this->call(RoleTableSeeder::class);
    }
}
