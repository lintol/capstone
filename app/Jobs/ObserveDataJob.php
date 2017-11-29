<?php

namespace App\Jobs;

use File;
use GuzzleHttp;
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
use Carbon\Carbon;

class ObserveDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $dataSession = [];

    public $validation = null;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info(__("Subscribing..."));

        $client = new Client('realm1');
        $client->addTransportProvider(new PawlTransportProvider('ws://172.18.0.1:8080/ws'));

        $client->on('open', function (ClientSession $session) {
            $session->subscribe('com.ltldoorstep.event_result',
                function ($res) use ($session) {
                    try {
                        $this->beginData($res[0], $res[1]);

                        Log::info("Found a completing validation job");

                        $validation = Validation::where('doorstep_server_id', '=', $res[0])
                            ->where('doorstep_session_id', '=', $res[1])
                            ->first();

                        if ($validation) {
                            Log::info("...it is one of ours.");

                            $context = ['validation' => $validation];
                            $this->runSteps($session, null, [
                                [$this, 'getReport'],
                                [$this, 'outputReport']
                            ], $context)->done(function ($res) use ($validation, $session) {
                                \Log::info('Publishing com.ltlcapstone.validation.' . $validation->id . '.event_complete');
                                return $session->publish(
                                    'com.ltlcapstone.validation.' . $validation->id . '.event_complete',
                                    [$validation->report]
                                );
                            }, function ($error) {
                                Log::info('error');
                                Log::error($error);
                            });
                        }
                    } catch (\Exception $e) {
                        Log::error($e);
                    }
                    Log::info("Finished [B]");
                }
            );

            $session->register('com.ltlcapstone.validation',
                function ($res) use ($session) {
                    try {
                        $dataUri = $res[0];

                        Log::info("Validation requested of " . $dataUri);

                        $validation = App::make(Validation::class);

                        $path = 'good';
                        $pData = File::get(resource_path('example/processors/good.py'));

                        $processor = App::make(Processor::class);
                        $processor->module = $path;
                        $processor->content = $pData;
                        $processor->save();
                        $validation->processor()->associate($processor);
                        $validation->save();

                        Log::info('Requesting data from ' . $dataUri);

                        $client = new GuzzleHttp\Client();
                        $request = new GuzzleHttp\Psr7\Request('GET', $dataUri);

                        $promise = $client->sendAsync($request)->then(function ($response) use ($dataUri, $validation) {
                            $path = basename($dataUri);
                            $dData = $response->getBody();

                            $data = App::make(Data::class);
                            $data->filename = $path;
                            $data->content = $dData;
                            $data->save();

                            $validation->data()->associate($data);
                            $validation->save();

                            ProcessDataJob::dispatch($validation->id);
                        }, function ($error) {
                            throw RuntimeException($error);
                        });

                        Log::info('Requested data');
                        $promise->wait();
                        Log::info('Finished [A]');

                        return $validation->id;
                    } catch (\Exception $e) {
                        Log::error($e);
                        throw $e;
                    }
                }
            );
        });

        $client->start();

        Log::info(__("Subscription exited."));
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

    protected function getReport($session, $previous, &$context) {
        return $session->call(
            $this->makeUri('report.get'),
            [$this->dataSession['session']]
        );
    }

    protected function outputReport($session, $previous, &$context) {
        $validation = $context['validation'];
        $validation->report = $previous;
        $validation->completed_at = Carbon::now();
        $validation->save();
    }

    protected function beginData($server, $session)
    {
        $this->dataSession = ['server' => $server, 'session' => $session];
    }
}
