<?php

namespace App\Jobs;

use File;
use Carbon\Carbon;
use App;
use App\Models\Validation;
use App\Models\Processor;
use App\Models\Data;
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

    public $dataSession = [];

    public $validationId = null;

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
    public function handle()
    {
        $validationId = $this->validationId;

        Log::info(__("Job running for validation ") . $validationId);

        $client = new Client('realm1');
        $client->addTransportProvider(new PawlTransportProvider('ws://172.18.0.1:8080/ws'));
        $client->setAttemptRetry(false);

        $client->on('open', function (ClientSession $session) use ($validationId, $client) {
            $session->call('com.ltldoorstep.engage')->then(
                function ($res) use ($session, $validationId) {
                    try {
                        $this->beginData($res[0][0], $res[0][1]);

                        $validation = App::make(Validation::class)->find($validationId);
                        $validation->doorstep_server_id = $res[0][0];
                        $validation->doorstep_session_id = $res[0][1];
                        $validation->requested_at = Carbon::now();
                        $validation->completion_status = Validation::STATUS_RUNNING;
                        $validation->save();

                        $context = ['validation' => $validation];
                        $this->runSteps($session, null, [
                            [$this, 'sendProcessor'],
                            [$this, 'sendData']
                        ], $context)->done(
                            function ($res) use ($session, $validation) {
                                $session->close();
                                $validation->initiated_at = Carbon::now();
                                $validation->save();
                                Log::info("Complete [C] for " . $validation->id);
                            },
                            function ($error) use ($session) {
                                $session->close();
                                Log::error($error);
                            }
                        );

                    } catch (\Exception $e) {
                        Log::info('error');

                        $session->close();
                    }
                    Log::info("Finished [C]");
                },
                function ($error) {
                    $session->close();
                    throw RuntimeException($error);
                }
            );
        });

        $client->start();

        Log::info(__("Job finished"));
    }

    protected function runSteps(&$session, $result, $steps, &$context)
    {
        if (empty($steps)) {
            return;
        }
        $step = array_shift($steps);

        $future = $step($session, $result, $context);

        if ($future) {
            return $future->then(
                function ($result) use (&$session, $steps, &$context) {
                    return $this->runSteps($session, $result, $steps, $context);
                },
                function ($error) {
                    throw RuntimeException($error);
                }
            );
        }
    }

    protected function makeUri($endpoint) {
        return 'com.ltldoorstep.' . $this->dataSession['server'] . '.' . $endpoint;
    }

    protected function sendProcessor($session, $previous, &$context) {
        $validation = $context['validation'];
        $processor = $validation->processor;

        $future = $session->call(
            $this->makeUri('processor.post'),
            [
                $this->dataSession['session'],
                $processor->module,
                $processor->content
            ]
        );
        return $future;
    }

    protected function sendData($session, $previous, &$context) {
        $validation = $context['validation'];
        $data = $validation->data;

        $future = $session->call(
            $this->makeUri('data.post'),
            [
                $this->dataSession['session'],
                $data->filename,
                $data->content
            ]
        );

        return $future;
    }

    protected function beginData($server, $session)
    {
        $this->dataSession = ['server' => $server, 'session' => $session];
    }
}
