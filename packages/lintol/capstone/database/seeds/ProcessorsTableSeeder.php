<?php

namespace Lintol\Capstone\Seeders;

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
            'name' => 'CSV Checking by CSVLint',
            'description' => 'ODI tool to processes tabular data',
            'unique_tag' => 'theodi/csvlint.rb:1',
            'module' => 'cl',
            'content' => '',
            'metadata' => [
                'docker' => [
                    'image' => 'lintol/ds-csvlint',
                    'revision' => 'latest'
                ]
            ]
        ]);
        $processor->creator()->associate($dataOwner);
        $processor->save();

        $processor = Processor::firstOrNew([
            'name' => 'CSV Checking by GoodTables',
            'description' => 'CSV checking tool from Frictionless Data project',
            'unique_tag' => 'frictionlessdata/goodtables-py:1',
            'module' => 'good',
            'content' => '',
            'metadata' => [
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
