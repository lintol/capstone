<?php

namespace Lintol\Capstone\Console\Commands;

use App;
use File;
use Lintol\Capstone\Models\Validation;
use Lintol\Capstone\Models\Processor;
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
                        $client = new \GuzzleHttp\Client();
        $validation = App::make(Validation::class);

        $path = 'csv_checker';
        $pData = File::get(resource_path('example/processors/csv_checker.py'));

        $processor = App::make(Processor::class);
        $processor->module = $path;
        $processor->content = $pData;
        $processor->save();

        $path = 'bad.csv';
        $dData = File::get(resource_path('example/data/bad.csv'));

        $data = App::make(Data::class);
        $data->filename = $path;
        $data->content = $dData;
        $data->save();

        $validation->data()->associate($data);
        $validation->processor()->associate($processor);
        $validation->save();

        $validationId = $validation->id;

        ProcessDataJob::dispatch($validationId);
    }
}
