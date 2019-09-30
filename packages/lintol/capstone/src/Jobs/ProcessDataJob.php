<?php

namespace Lintol\Capstone\Jobs;

use File;
use App;
use Lintol\Capstone\Models\ValidationRun;
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
use Lintol\Capstone\WampConnection;
use Illuminate\Http\Exceptions\ThrottleRequestsException;

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
    public function __construct($validationId)
    {
        $this->validationId = $validationId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(ValidationProcess $processFactory, WampConnection $wampConnection)
    {
        Log::info(__("Job running for validation ") . $this->validationId);

        $wampConnection->execute(function (ClientSession $session) use ($processFactory) {
            $process = $processFactory->make($this->validationId, $session);
            $promise = $process->run()
                ->otherwise(function (ThrottleRequestsException $e) {
                    $retryDelay = config('capstone.wamp.doorstep-retry-delay', 120);
                    Log::warn(__("Throttled job for ") . $retryDelay . "s");
                    $this->release($retryDelay);
                })
                ->always(function () { \Log::info("ENDED"); });
            return $promise;
        });

        Log::info(__("Client exited"));
    }

    public function tags()
    {
        return ['validation-outgoing', 'process'];
    }
}
