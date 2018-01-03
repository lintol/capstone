<?php

namespace Lintol\Capstone\Console\Commands;

use Log;
use App;
use File;
use Lintol\Capstone\Models\Validation;
use Lintol\Capstone\Models\Processor;
use Lintol\Capstone\Models\ProcessorConfiguration;
use Lintol\Capstone\Models\Profile;
use Lintol\Capstone\Models\Data;
use Illuminate\Console\Command;
use Lintol\Capstone\Jobs\ProcessDataJob;

class ProcessDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ltl:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process a dataset';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $validationId = $this->exampleValidationLaunch();

        ProcessDataJob::dispatch($validationId)
            ->onConnection('sync');
    }

    public function exampleValidationLaunch()
    {
        $validation = App::make(Validation::class);

        $path = 'good';
        $pData = File::get(__DIR__ . '/../../../examples/processors/good.py');

        $tag = 'frictionlessdata/goodtables-py:1';

        $profile = App::make(Profile::class)->firstOrNew(['unique_tag' => 'test-goodtables-1']);
        if (true || !$profile) {
            $profile->name = "Test Goodtables";
            $profile->description = "Testing goodtables";
            $profile->version = '1';
            $profile->unique_tag = 'test-goodtables-1';
            $profile->save();

            $configuration = App::make(ProcessorConfiguration::class);
            $configuration->configuration = [];
            $configuration->metadata = [];
            $configuration->rules = ['fileType' => '/csv/'];

            $configuration->profile()->associate($profile);
            $configuration->save();

            $processor = App::make(Processor::class)->firstOrNew(['unique_tag' => $tag]);

            if (!$processor->id) {
                $processor->name = "Example Goodtables";
                $processor->description = "Example showing cross-over with Goodtables";
                $processor->unique_tag = $tag;
                $processor->module = $path;
                $processor->content = $pData;
                $processor->save();
            }
            $configuration->processor()->associate($processor);
            $configuration->save();
        }

        $configuration = $profile->configurations[0];

        $validation->configuration()->associate($configuration);
        $validation->buildMetadata(['test123']);
        $validation->save();

        $path = 'bad.csv';
        $dData = File::get(resource_path('capstone/examples/data/bad.csv'));

        $data = App::make(Data::class);
        $data->filename = $path;
        $data->content = $dData;
        $data->save();

        $validation->data()->associate($data);
        $validation->save();

        return $validation->id;
    }
}
