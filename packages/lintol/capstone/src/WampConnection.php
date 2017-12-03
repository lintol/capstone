<?php

namespace Lintol\Capstone;

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

class WampConnection
{
    protected $realm;

    protected $session = null;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($realm)
    {
        $this->realm = $realm;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function execute(callable $closure)
    {
        if ($this->session) {
            $closure($this->session);
        } else {
            $client = new Client($this->realm);
            $client->addTransportProvider(new PawlTransportProvider(''));
            $client->setAttemptRetry(false);

            $client->on('open', function (ClientSession $session) use ($closure) {
                $this->session = $session;

                try {
                    $closure($session);
                } catch (Exception $e) {
                    Log::error($error);
                }

                $this->session = null;
                $session->close();
            });

            $client->start();
        }
    }
}
