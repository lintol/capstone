<?php

namespace Lintol\Capstone\Jobs;

use File;
use GuzzleHttp;
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
use Carbon\Carbon;
use Lintol\Capstone\WampConnection;

class ObserveDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $validation = null;

    protected $processFactory;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(ValidationProcess $processFactory, WampConnection $wampConnection)
    {
        Log::info(__("Subscribing."));

        $wampConnection->execute(function (ClientSession $session) use ($processFactory) {
                Log::info("[lintol-observe] " . __("Connected and subscribing to result events."));

                $session->subscribe('com.ltldoorstep.event_result', function ($res) use ($session, $processFactory) {
                    $process = $processFactory->fromDataSession($res[0], $res[1], $session);

                    Log::debug("[lintol-observe] " . __("Validation result event seen."));

                    if ($process) {
                        Log::info("[lintol-observe] " . __("Incoming validation is in our database."));

                        $process->retrieve();
                    }
                });

                $session->register(
                    'com.ltlcapstone.validation',
                    function ($res) use ($session) {
                        $dataUri = $res[0];
                        $settings = $res[1];
                        return $this->exampleValidationLaunch($dataUri, $settings);
                    }
                );
        }, false);

        Log::info(__("Subscription exited."));
    }

    public function exampleValidationLaunch($dataUri, $settings)
    {
        Log::info(__("Validation requested of ") . $dataUri);

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

            $settings = ['fileType' => 'csv', 'name' => 'Goodtables test'];
            $profiles = App::make(Profile::class)->match($settings);
            $profiles->map(function ($profile) use ($data, $settings) {
                $run = App::make(ValidationRun::class);

                $run->profile()->associate($profile);
                if (!$run->buildDefinition($settings)) {
                    return null;
                }

                $run->save();

                $run->data()->associate($data);
                $run->save();

                return $run;
            })
            ->filter()
            ->each(function ($run) {
                ProcessDataJob::dispatch($run->id);
            });
        }, function ($error) {
            throw RuntimeException($error);
        });

        Log::info('Requested data');

        $promise->wait();

        return $validation->id;
    }
}
