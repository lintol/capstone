<?php

namespace Lintol\Capstone\Jobs;

use File;
use GuzzleHttp;
use App;
use Lintol\Capstone\Models\ValidationRun;
use Lintol\Capstone\Models\Processor;
use Lintol\Capstone\Models\DataResource;
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

        $data = app()->make(DataResource::class);
        $data->name = $dataUri;
        $data->settings = $settings;
        $data->url = $dataUri;

        $client = new GuzzleHttp\Client();
        $request = new GuzzleHttp\Psr7\Request('GET', $data->url);

        $promise = $client->sendAsync($request)->then(function ($response) use ($data) {
            $path = basename($data->url);
            $dData = $response->getBody();

            $data->filename = $path;
            $data->name = $path;
            $data->filetype = $data->settings['fileType'];
            $data->content = $dData;
            $data->save();

            return ValidationProcess::launch($data);
        }, function ($error) {
            abort(400, __("Invalid data URI request"));
        });

        $runs = $promise->wait();

        return $runs->first()->id;
    }
}
