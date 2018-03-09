<?php

namespace Lintol\Capstone\Console\Commands;

use Log;
use App;
use File;
use Lintol\Capstone\Models\ValidationRun;
use Lintol\Capstone\Models\Processor;
use Lintol\Capstone\Models\ProcessorConfiguration;
use Lintol\Capstone\Models\Profile;
use Lintol\Capstone\Models\DataResource;
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

    protected $rulesService;

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
        $runs = $this->exampleValidationLaunch();

        $runs->each(function ($run) {
            ProcessDataJob::dispatch($run->id)
                ->onConnection('sync');
        });
    }

    public function exampleValidationLaunch()
    {
        $path = 'good';
        $pData = File::get(__DIR__ . '/../../../examples/processors/goodtables/good.py');

        $tag = 'frictionlessdata/goodtables-py:1';

        $profile = App::make(Profile::class)->firstOrNew(['unique_tag' => 'test-goodtables-1']);
        $profile->name = "Test Goodtables";
        $profile->description = "Testing goodtables";
        $profile->version = '1';
        $profile->unique_tag = 'test-goodtables-1';
        $profile->rules = ['fileType' => 'csv', 'name' => '/Goodtables/'];
        $profile->save();

        $profile->configurations()->delete();
        $configuration = App::make(ProcessorConfiguration::class);
        $configuration->profile()->associate($profile);

        $processor = App::make(Processor::class)->whereUniqueTag($tag)->firstOrFail();

        $configuration->processor()->associate($processor);
        $configuration->save();

        $path = 'awful.csv';
        $dData = File::get(resource_path('capstone/examples/data/awful.csv'));

        $data = App::make(DataResource::class);
        $data->filename = $path;
        $data->content = $dData;
        $data->save();

        $settings = ['fileType' => 'csv', 'name' => 'Goodtables test'];
        $profiles = App::make(Profile::class)->match($settings);
        $runs = $profiles->map(function ($profile) use ($data, $settings) {
            $run = App::make(ValidationRun::class);

            $run->profile()->associate($profile);
            if (!$run->buildDefinition($settings)) {
                return null;
            }

            $run->save();

            $run->dataResource()->associate($data);
            $run->save();

            return $run;
        })->filter();

        return $runs;
    }
}
