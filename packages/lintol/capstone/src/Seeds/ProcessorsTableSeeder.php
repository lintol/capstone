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
            'module' => 'cl.rb',
            'content' => '',
            'rules' => ['fileType' => '/csv/'],
            'configuration_defaults' => [
                'delimiter' => 'comma'
            ],
            'configuration_options' => json_encode([
                'fields' => [
                    [
                      'type' => 'select',
                      'label' => 'Delimiter',
                      'model' => 'delimiter',
                      'required' => true,
                      'values' => [
                        [ 'id' => 'comma', 'name' => 'Comma' ],
                        [ 'id' => 'tab', 'name' => 'Tab'  ]
                      ]
                    ]
                ]
            ]),
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
            'module' => 'good.py',
            'content' => File::get($processorsPath . 'good.py'),
            'rules' => ['fileType' => '/csv/'],
            'configuration_defaults' => [
                'delimiter' => 'comma'
            ],
            'configuration_options' => json_encode([
                'fields' => [
                    [
                      'type' => 'select',
                      'label' => 'Delimiter',
                      'model' => 'delimiter',
                      'required' => true,
                      'values' => [
                        [ 'id' => 'comma', 'name' => 'Comma' ],
                        [ 'id' => 'tab', 'name' => 'Tab'  ]
                      ]
                    ]
                ]
            ]),
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
            'unique_tag' => 'lintol/ds-pii-legacy:1',
        ]);
        $processor->fill([
            'name' => 'Personally-Identifiable Information Spotter',
            'description' => 'Tool for searching for Personally-Identifiable Information within CSV data',
            'module' => 'pii_legacy.py',
            'content' => File::get($processorsPath . 'pii_legacy.py'),
            'rules' => [],
            'definition' => [
                'docker' => [
                    'image' => 'lintol/ds-pii-legacy',
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
            'name' => 'Boundary Checker (impr)',
            'description' => 'GeoJSON boundary checker to make sure data is within given boundaries',
            'module' => 'boundary_checker_impr.py',
            'content' => File::get($processorsPath . 'boundary_checker_impr.py'),
            'rules' => ['fileType' => '/geojson/'],
            'definition' => [
                'docker' => [
                    'image' => 'lintol/doorstep',
                    'revision' => 'latest'
                ]
            ],
            'supplementary_links' => [
              'GB-NIR' => 'https://s3.eu-west-2.amazonaws.com/resources.lintol.io/osni-ni-outline-lowres.geojson',
              'GB-NIR:Settlement:Belfast' => 'https://s3.eu-west-2.amazonaws.com/resources.lintol.io/settlement-boundaries/belfast-settlement-development.geojson',
              'GB-NIR:Settlement:Strabane' => 'https://s3.eu-west-2.amazonaws.com/resources.lintol.io/settlement-boundaries/strabane-settlement-development.geojson',
              'GB-NIR:Settlement:Enniskillen' => 'https://s3.eu-west-2.amazonaws.com/resources.lintol.io/settlement-boundaries/enniskillen-settlement-development.geojson'
            ],
            'configuration_defaults' => [
                'boundary' => '$GB-FMO'
            ],
            'configuration_options' => json_encode([
                'fields' => [
                    [
                      'type' => 'select',
                      'label' => 'Boundary',
                      'model' => 'boundary',
                      'required' => true,
                      'values' => [
                        [ 'id' => '$->GB-NIR', 'name' => 'Northern Ireland' ],
                        [ 'id' => '$->GB-NIR:Settlement:Belfast', 'name' => 'Belfast'  ],
                        [ 'id' => '$->GB-NIR:Settlement:Enniskillen', 'name' => 'Enniskillen'  ],
                        [ 'id' => '$->GB-NIR:Settlement:Strabane', 'name' => 'Strabane'  ],
                      ]
                    ]
                ]
            ])
        ]);
        $processor->creator()->associate($dataOwner);
        $processor->save();

        $processor = Processor::firstOrNew([
            'unique_tag' => 'lintol/ds-checker-py',
        ]);
        $processor->fill([
            'name' => 'gov.uk Register Checker - Countries',
            'description' => 'Check that CSV data about countries matches gov.uk register entries',
            'module' => 'registers.py',
            'content' => File::get($processorsPath . 'registers.py'),
            'rules' => ['fileType' => '/csv/'],
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
            'unique_tag' => 'datatimes/dt-classify-category:1',
        ]);
        $processor->fill([
            'name' => 'Data Times Category Classifier',
            'description' => 'NLP category classifier for tagging datasets',
            'module' => 'dt_classify_category.py',
            'content' => File::get($processorsPath . 'dt_classify_category.py'),
            'rules' => ['fileType' => '//'],
            'configuration_defaults' => [
                'categoryServerUrl' => config('capstone.examples.classify-category.category-server-url', 'http://localhost:5000/'),
                'metadataOnly' => true
            ],
            'configuration_options' => json_encode([
            ]),
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
            'unique_tag' => 'datatimes/dt-classify-location:1',
        ]);
        $processor->fill([
            'name' => 'Data Times Location Classifier',
            'description' => 'NLP location classifier for tagging datasets',
            'module' => 'dt_classify_location.py',
            'content' => '',
            'rules' => ['fileType' => '/(csv|json|geojson)/', 'maxSize' => 100000],
            'configuration_defaults' => [
                'renderCodes' => config('capstone.examples.classify-location.render-codes', True),
                'metadataOnly' => false
            ],
            'configuration_options' => json_encode([
            ]),
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
            'unique_tag' => 'datatimes/dt-comprehender:1',
        ]);
        $processor->fill([
            'name' => 'Data Times Comprehender',
            'description' => 'Compehension processor for inferring the nature of datasets',
            'module' => 'dt_comprehender.py',
            'content' => '',
            'rules' => ['fileType' => '/(csv|json|geojson)/', 'maxSize' => 100000],
            'configuration_defaults' => [
                'metadataOnly' => false
            ],
            'configuration_options' => json_encode([
            ]),
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
