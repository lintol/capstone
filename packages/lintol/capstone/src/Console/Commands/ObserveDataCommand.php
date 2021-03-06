<?php

namespace Lintol\Capstone\Console\Commands;

use Illuminate\Console\Command;
use Lintol\Capstone\Jobs\ObserveDataJob;

class ObserveDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ltl:observe';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Watch for validation results';

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
        ObserveDataJob::dispatch()
            ->onConnection('sync');
    }
}
