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
use Illuminate\Support\Facades\Log;

/**
 * Class WampConnection
 * @package Lintol\Capstone
 */
class WampConnection
{
    protected $realm;

    protected $session = null;

    protected $stayOpen = false;

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

    public function setStayOpen(bool $stayOpen)
    {
        $this->stayOpen = $stayOpen;
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
        Log::debug($closeOnComplete ? 'Y' : 'N');
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
                    Log::debug('promise on way');
                } catch (Exception $error) {
                    Log::error('Error Closing sessions' . $error);
                    $close = true;
                }
                Log::debug($close);

                if ($close && ! $this->stayOpen) {
                    Log::debug('promise to close');
                    $this->session = null;
                    if ($promise) {
                        Log::debug('promise to always');
                        $promise->always(function () use ($session) {
                            Log::debug('closing session');
                            $session->close();
                        });
                    } else {
                        $session->close();
                    }
                }
            });

            $client->start();
        }
    }
}
