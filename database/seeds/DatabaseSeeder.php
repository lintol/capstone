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
        $this->call(Lintol\Capstone\Seeds\ProcessorsTableSeeder::class);
        $this->call(Seeders\RolesPermissionsSeeder::class);
    }
}
