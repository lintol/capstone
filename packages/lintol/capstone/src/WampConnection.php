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
    public function __construct($url, $realm)
    {
        $this->url = $url;
        $this->realm = $realm;
    }

    /**
     * Execute the job.
     *
     * @param callable $closure Callback to use the session
     * @param boolean $closeOnComplete Whether to close the session on call-back exit (ignored if session already opened)
     * @return void
     */
    public function execute(callable $closure, $closeOnComplete = true)
    {
        \Log::info($closeOnComplete ? 'Y' : 'N');
        if ($this->session) {
            $closure($this->session);
        } else {
            $client = new Client($this->realm);
            $client->addTransportProvider(new PawlTransportProvider($this->url));
            $client->setAttemptRetry(false);

            $client->on('open', function (ClientSession $session) use ($closure, $closeOnComplete) {
                $this->session = $session;
                $close = $closeOnComplete;

                try {
                    $promise = $closure($session);
                    \Log::info('promise on way');
                } catch (Exception $e) {
                    Log::error($error);
                    $close = true;
                }
                \Log::info($close);

                if ($close) {
                    \Log::info('promise to close');
                    $this->session = null;
                    if ($promise) {
                        \Log::info('promise to always');
                        $promise->always(function () use ($session) { $session->close(); });
                    } else {
                        $session->close();
                    }
                }
            });

            $client->start();
        }
    }
}
