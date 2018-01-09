<?php

namespace Seeders\Sample;

use Illuminate\Database\Seeder;
use Lintol\Capstone\Models\Report;
use App\User;

class ReportsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $dataOwner = User::whereEmail('do@example.com')->first();

        $report = Report::firstOrNew([
            'name' => 'Data Profile Name [test]',
        ]);
        $report->fill([
            'errors' => '7',
            'warnings' => '44',
            'passes' => '44',
            'quality_score' => '76',
            'content' => []
        ]);
        $report->owner()->associate($dataOwner);
        $report->save();

        $report = Report::firstOrNew([
            'name' => 'Data Profile Name [test]',
        ]);
        $report->fill([
            'errors' => '7',
            'warnings' => '44',
            'passes' => '10',
            'quality_score' => '60',
            'content' => []
        ]);
        $report->owner()->associate($dataOwner);
        $report->save();

        $report = Report::firstOrNew([
            'name' => 'Data Profile Name [test]',
        ]);
        $report->fill([
            'errors' => '7',
            'warnings' => '44',
            'passes' => '3',
            'quality_score' => '30',
            'content' => []
        ]);
        $report->owner()->associate($dataOwner);
        $report->save();

        $report = Report::firstOrNew([
            'name' => 'Data Profile Name [test]',
        ]);
        $report->fill([
            'errors' => '50',
            'warnings' => '44',
            'passes' => '0',
            'quality_score' => '44',
            'content' => []
        ]);
        $report->owner()->associate($dataOwner);
        $report->save();

        $report = Report::firstOrNew([
            'name' => 'Data Profile Name [test]',
        ]);
        $report->fill([
            'errors' => '7',
            'warnings' => '40',
            'passes' => '1',
            'quality_score' => '40',
            'content' => []
        ]);
        $report->owner()->associate($dataOwner);
        $report->save();
    }
}
