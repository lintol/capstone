<?php

namespace Lintol\Capstone\Console\Commands;

use Illuminate\Console\Command;
use Lintol\Capstone\Jobs\ProcessDataJob;
use Lintol\Capstone\Models\Report;
use Lintol\Capstone\Models\ValidationRun;

class RerunValidationRunCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ltl:rerun {type} {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Re-run a Validation Run';

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
        $id = $this->argument('id');
        $type = $this->argument('type');

        if ($type == 'report') {
            $report = Report::findOrFail($id);
            $run = $report->run;
            if (! $run) {
                throw RuntimeException(__("This report has no matching validation run."));
            }
        } else if ($type == 'run') {
            $run = ValidationRun::findOrFail($id);
        } else {
            throw RuntimeException(__("The type should be either 'report' or 'run'."));
        }

        $newRun = $run->duplicate();
        $newRun->save();

        ProcessDataJob::dispatch($newRun->id);
    }
}
