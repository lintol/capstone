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
        $this->call(Seeders\Sample\UserSeeder::class);
        $this->command->info("Set up Data seeded");
        
        /* Sample Data */ 
        $this->call(Lintol\Capstone\Seeds\Sample\ProfilesTableSeeder::class);
        $this->call(Lintol\Capstone\Seeds\Sample\ReportsTableSeeder::class);
        $this->command->info("Sample Data Seeded");
        
    }
}
