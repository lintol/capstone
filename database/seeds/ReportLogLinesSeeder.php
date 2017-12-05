<?php

use Illuminate\Database\Seeder;

class ReportLogLinesSeeder extends Seeder
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
            'ragType' => 'error',
            'message' => 'There was an error in',
            'processor' => 'CSV Lint',
            'detail' => 'There a lot of errors in this space of the CSV',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        DB::table('reports')->insert([
            'ragType' => 'warning',
            'message' => 'There was a warning in',
            'processor' => 'CSV Lint',
            'detail' => 'There a lot of warnings in this space of the CSV',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        DB::table('reports')->insert([
            'ragType' => 'pass',
            'message' => 'The validation passed',
            'processor' => 'JSON Lint',
            'detail' => 'There a lot of passes in this space of the JSON',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        DB::table('reports')->insert([
            'ragType' => 'errors',
            'message' => 'There was an errors in',
            'processor' => 'RDF Lint',
            'detail' => 'There a lot of errors in this space of the RDF',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        DB::table('reports')->insert([
            'ragType' => 'warning',
            'message' => 'There was an warning in',
            'processor' => 'CSV Lint',
            'detail' => 'There a lot of warning in this space of the CSV',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        DB::table('reports')->insert([
            'ragType' => 'pass',
            'message' => 'The validation passed',
            'processor' => 'CSV Lint',
            'detail' => 'There a lot of passes in this space of the CSV',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
}
