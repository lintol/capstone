<?php

namespace Lintol\Capstone\Seeds\Sample;

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
            'name' => 'Data Profile Name [test1]',
        ]);
        $report->fill([
            'errors' => '7',
            'warnings' => '44',
            'passes' => '44',
            'quality_score' => '76',
            'content' => '{
               "table-count" : 1,
               "warnings" : [],
               "error-count" : 3,
               "tables" : [
                  {
                     "schema" : null,
                     "time" : 0.032,
                     "source" : "data/awful.csv",
                     "encoding" : "utf-8",
                     "scheme" : "file",
                     "errors" : [
                        {
                          "processor": "csvlint",
                           "code" : "duplicate-header",
                           "row-number" : null,
                           "message" : "Header in column 4 is duplicated to header in column(s) 2",
                           "row" : null,
                           "column-number" : 4
                        },
                        {
                          "processor": "goodtables",
                           "row" : [
                              "4",
                              "Salad",
                              "3"
                           ],
                           "message" : "Row 5 has a missing value in column 4",
                           "column-number" : 4,
                           "code" : "missing-value",
                           "row-number" : 5
                        },
                        {
                          "processor": "goodtables",
                           "column-number" : 5,
                           "message" : "Row 5 has a missing value in column 5",
                           "row" : [
                              "4",
                              "Salad",
                              "3"
                           ],
                           "row-number" : 5,
                           "code" : "missing-value"
                        }
                     ],
                     "format" : "csv",
                     "row-count" : 5,
                     "valid" : false,
                     "headers" : [
                        "ID",
                        "Mean",
                        "N",
                        "Mean",
                        "Standard Deviation"
                     ],
                     "error-count" : 3
                  }
               ],
               "preset" : "table",
               "valid" : false,
               "time" : 0.035
            }'
        ]);
        $report->owner()->associate($dataOwner);
        $report->save();

        $report = Report::firstOrNew([
            'name' => 'Data Profile Name [test2]',
        ]);
        $report->fill([
            'errors' => '7',
            'warnings' => '44',
            'passes' => '10',
            'quality_score' => '60',
            'content' => '{
               "table-count" : 1,
               "warnings" : [],
               "error-count" : 3,
               "tables" : [
                  {
                     "schema" : null,
                     "time" : 0.032,
                     "source" : "data/awful.csv",
                     "encoding" : "utf-8",
                     "scheme" : "file",
                     "errors" : [
                        {
                          "processor": "csvlint",
                           "code" : "duplicate-header",
                           "row-number" : null,
                           "message" : "Header in column 4 is duplicated to header in column(s) 2",
                           "row" : null,
                           "column-number" : 4
                        },
                        {
                          "processor": "goodtables",
                           "row" : [
                              "4",
                              "Salad",
                              "3"
                           ],
                           "message" : "Row 5 has a missing value in column 4",
                           "column-number" : 4,
                           "code" : "missing-value",
                           "row-number" : 5
                        },
                        {
                          "processor": "goodtables",
                           "column-number" : 5,
                           "message" : "Row 5 has a missing value in column 5",
                           "row" : [
                              "4",
                              "Salad",
                              "3"
                           ],
                           "row-number" : 5,
                           "code" : "missing-value"
                        }
                     ],
                     "format" : "csv",
                     "row-count" : 5,
                     "valid" : false,
                     "headers" : [
                        "ID",
                        "Mean",
                        "N",
                        "Mean",
                        "Standard Deviation"
                     ],
                     "error-count" : 3
                  }
               ],
               "preset" : "table",
               "valid" : false,
               "time" : 0.035
            }'
        ]);
        $report->owner()->associate($dataOwner);
        $report->save();

        $report = Report::firstOrNew([
            'name' => 'Data Profile Name [test3]',
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
            'name' => 'Data Profile Name [test4]',
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
            'name' => 'Data Profile Name [test5]',
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
