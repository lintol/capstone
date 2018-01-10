<?php

namespace Seeders\Sample;

use Illuminate\Database\Seeder;
use Lintol\Capstone\Models\Processor;
use App\User;

class ProcessorsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $dataOwner = User::whereEmail('do@example.com')->first();

        $processor = Processor::firstOrNew([
            'name' => 'csv processor [test]',
            'description' => 'processes csv files',
            'unique_tag' => 'csv-101',
            'module' => 'csv_processor',
            'content' => ''
        ]);
        $processor->creator()->associate($dataOwner);
        $processor->save();

        $processor = Processor::firstOrNew([
            'name' => 'json processor [test]',
            'description' => 'processes json files',
            'unique_tag' => 'json-102',
            'module' => 'json_processor',
            'content' => ''
        ]);
        $processor->creator()->associate($dataOwner);
        $processor->save();

        $processor = Processor::firstOrNew([
            'name' => 'rdf processor [test]',
            'description' => 'processes csv files',
            'unique_tag' => 'rdf-101',
            'module' => 'rdf_processor',
            'content' => ''
        ]);
        $processor->creator()->associate($dataOwner);
        $processor->save();

        $processor = Processor::firstOrNew([
            'name' => 'xml processor [test]',
            'description' => 'processes xml files',
            'unique_tag' => 'xml-101',
            'module' => 'xml_processor',
            'content' => ''
        ]);
        $processor->creator()->associate($dataOwner);
        $processor->save();

        $processor = Processor::firstOrNew([
            'name' => 'xls processor [test]',
            'description' => 'processes xls files',
            'unique_tag' => 'xls-101',
            'module' => 'xls_processor',
            'content' => ''
        ]);
        $processor->creator()->associate($dataOwner);
        $processor->save();

    }
}
