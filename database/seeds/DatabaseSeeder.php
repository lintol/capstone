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
        $this->call(Lintol\Capstone\Seeds\ProcessorsTableSeeder::class,
        Seeders\RolesPermissionsSeeder::class);
        $this->command->info("Set up Data seeded");
        
        /* Sample Data */ 
        $this->call(Lintol\Capstone\Seeds\Sample\ProfilesTableSeeder::class,
        Lintol\Capstone\Seeds\Sample\ReportsTableSeeder::class);
        $this->command->info("Sample Data Seeded");
    }
}
