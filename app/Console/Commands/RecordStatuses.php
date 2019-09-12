<?php

namespace App\Console\Commands;

use Crypt;
use Queue;
use App\StatusTracking;
use Lintol\Capstone\Models\DataResource;
use Lintol\Capstone\Models\ValidationRun;
use Illuminate\Console\Command;

class RecordStatusesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ltl:record-statuses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $statusTracking = new StatusTracking;
        $dataResourceModel = app()->make(DataResource::class);
        $validationRunModel = app()->make(ValidationRun::class);

        $statusTracking->statuses = [
            'resource_statuses' => $dataResourceModel->summaryByStatus(),
            'run_statuses' => $validationRunModel->summaryByStatus(),
            'jobs' => Queue::size()
        ];
        $statusTracking->save();
    }
}
