<?php

namespace Seeders\Sample;

use Illuminate\Database\Seeder;

class SampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UserSeeder::class);
        $this->call(ProcessorsTableSeeder::class);
        $this->call(ProfilesTableSeeder::class);
        $this->call(ReportsTableSeeder::class);
    }
}
