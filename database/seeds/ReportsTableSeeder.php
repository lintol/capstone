<?php

use Illuminate\Database\Seeder;

class ReportsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('reports')->insert([
            'name' => 'Data Profile Name',
            'user' => 'Martin',
            'errors' => '7',
            'warnings' => '44',
            'passes' => '44',
            'qualityScore' => '76',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        DB::table('reports')->insert([
            'name' => 'Data Profile Name',
            'user' => 'Martin',
            'errors' => '7',
            'warnings' => '44',
            'passes' => '10',
            'qualityScore' => '60',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        DB::table('reports')->insert([
            'name' => 'Data Profile Name',
            'user' => 'Martin',
            'errors' => '7',
            'warnings' => '44',
            'passes' => '3',
            'qualityScore' => '30',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        DB::table('reports')->insert([
            'name' => 'Data Profile Name',
            'user' => 'Martin',
            'errors' => '50',
            'warnings' => '44',
            'passes' => '0',
            'qualityScore' => '44',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        DB::table('reports')->insert([
            'name' => 'Data Profile Name',
            'user' => 'Martin',
            'errors' => '7',
            'warnings' => '40',
            'passes' => '1',
            'qualityScore' => '40',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
}
