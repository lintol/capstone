<?php

namespace Lintol\Capstone\Seeds;

use File;
use Illuminate\Database\Seeder;
use Lintol\Capstone\Models\Processor;
use Lintol\Capstone\Models\Rule;
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
        //$this->call(RulesTableSeeder::class);

        $processorsPath = __DIR__ . '/../../examples/processors/';
        $dataOwner = User::whereEmail('do@example.com')->first();

        $processor = Processor::firstOrNew(['unique_tag' => 'theodi/csvlint.rb:1']);
        $processor->fill([
            'name' => 'CSV Checking by CSVLint',
            'description' => 'ODI tool to processes tabular data',
            'module' => 'cl',
            'content' => '',
            'rules' => ['name' => '/[Ll]int/', 'fileType' => 'csv'],
            'definition' => [
                'docker' => [
                    'image' => 'lintol/ds-csvlint',
                    'revision' => 'latest'
                ]
            ]
        ]);
        $processor->creator()->associate($dataOwner);
        $processor->save();

        $processor = Processor::firstOrNew([
            'unique_tag' => 'frictionlessdata/goodtables-py:1',
        ]);
        $processor->fill([
            'name' => 'CSV Checking by GoodTables',
            'description' => 'CSV checking tool from Frictionless Data project',
            'module' => 'good',
            'content' => File::get($processorsPath . 'goodtables/good.py'),
            'rules' => ['name' => '/[Gg]ood[Tt]ables/', 'fileType' => 'csv'],
            'definition' => [
                'docker' => [
                    'image' => 'lintol/doorstep',
                    'revision' => 'latest'
                ]
            ]
        ]);
        $processor->creator()->associate($dataOwner);
        $processor->save();

        $processor = Processor::firstOrNew([
            'unique_tag' => 'lintol/ds-pii:1',
        ]);
        $processor->fill([
            'name' => 'Personally-Identifiable Information Spotter',
            'description' => 'Tool for searching for Personally-Identifiable Information within CSV data',
            'module' => 'pii',
            'content' => File::get($processorsPath . 'pii/pii.py'),
            'rules' => ['name' => '/PII/', 'fileType' => 'csv'],
            'definition' => [
                'docker' => [
                    'image' => 'lintol/doorstep',
                    'revision' => 'latest'
                ]
            ]
        ]);
        $processor->creator()->associate($dataOwner);
        $processor->save();

        $processor = Processor::firstOrNew([
            'unique_tag' => 'lintol/ds-boundary-checker-py:1',
        ]);
        $processor->fill([
            'name' => 'Boundary Checker',
            'description' => 'GeoJSON boundary checker to make sure data is within given boundaries',
            'module' => 'boundary_checker',
            'content' => File::get($processorsPath . 'boundary_checker.py'),
            'rules' => ['name' => '/[Bb]oundar/', 'fileType' => 'csv'],
            'definition' => [
                'docker' => [
                    'image' => 'lintol/doorstep',
                    'revision' => 'latest'
                ]
            ]
        ]);
        $processor->creator()->associate($dataOwner);
        $processor->save();

        $processor = Processor::firstOrNew([
            'unique_tag' => 'lintol/ds-checker-py',
        ]);
        $processor->fill([
            'name' => 'gov.uk Register Checker - Countries',
            'description' => 'Check that CSV data about countries matches gov.uk register entries',
            'module' => 'registers',
            'content' => File::get($processorsPath . 'registers.py'),
            'rules' => ['name' => '/[Cc]ountr/', 'fileType' => 'csv'],
            'definition' => [
                'docker' => [
                    'image' => 'lintol/doorstep',
                    'revision' => 'latest'
                ]
            ]
        ]);
        $processor->creator()->associate($dataOwner);
        $processor->save();
    }
}