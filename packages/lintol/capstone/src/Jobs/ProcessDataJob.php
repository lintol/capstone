<?php

namespace Lintol\Capstone\Jobs;

use File;
use App;
use Lintol\Capstone\Models\Validation;
use Lintol\Capstone\Models\Processor;
use Lintol\Capstone\Models\Data;
use Lintol\Capstone\ValidationProcess;

use Thruway\ClientSession;
use Thruway\Peer\Client;
use Thruway\Transport\PawlTransportProvider;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Log;

class ProcessDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $validationId = null;

    protected $processFactory;

    protected $wampConnection;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($validationId, ValidationProcess $validationProcess, WampConnection $wampConnection)
    {
        $this->validationId = $validationId;
        $this->processFactory = $validationProcess;
        $this->wampConnection = $wampConnection;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info(__("Job running for validation ") . $this->validationId);

        $this->wampConnection->execute(function (ClientSession $session) {
            $process = $this->processFactory->make($this->validationId, $session);
            $process->run();
        });

        Log::info(__("Client exited"));
    }
}
