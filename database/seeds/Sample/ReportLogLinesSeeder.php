<?php

namespace Seeders\Sample;

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
        for ($i = 0 ; $i < 6 ; $i++) {
            DB::table('reports')->insert([
                'ragType' => 'error',
                'message' => 'There was an error in',
                'processor' => 'CSV Lint',
                'detail' => 'There a lot of errors in this space of the CSV',
            ]);
        }
    }
}
