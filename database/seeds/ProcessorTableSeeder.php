<?php

use Illuminate\Database\Seeder;

class ProcessorTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('processors')->insert([
            'name' => 'csv processor',
            'description' => 'processes csv files',
            'uniqueTag' => 'csv-101',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')

        ]);
        DB::table('processors')->insert([
            'name' => 'json processor',
            'description' => 'processes json files',
            'uniqueTag' => 'json-101',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')

        ]);
        DB::table('processors')->insert([
            'name' => 'rdf processor',
            'description' => 'processes csv files',
            'uniqueTag' => 'rdf-101',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')

        ]);
        DB::table('processors')->insert([
            'name' => 'xml processor',
            'description' => 'processes xml files',
            'uniqueTag' => 'xml-101',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')

        ]);
        DB::table('processors')->insert([
            'name' => 'xls processor',
            'description' => 'processes xls files',
            'uniqueTag' => 'xls-101',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')

        ]);
    }
}
