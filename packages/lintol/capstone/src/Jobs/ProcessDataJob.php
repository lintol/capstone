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
use Lintol\Capstone\WampConnection;

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
            return $process->run();
        });

        Log::info(__("Client exited"));
    }

    public function exampleValidationLaunch($dataUri, $metadata)
    {
        Log::info(__("Validation requested of ") . $dataUri);

        $validation = App::make(Validation::class);

        $path = 'good';
        $pData = File::get(__DIR__ . '/../../examples/processors/good.py');

        $tag = 'frictionlessdata/goodtables-py:1';

        $profile = App::make(Profile::class)->firstOrNew(['unique_tag' => 'test-goodtables-1']);
        if (!$profile) {
            $profile->name = "Test Goodtables";
            $profile->description = "Testing goodtables";
            $profile->version = '1';
            $profile->unique_tag = 'test-goodtables-1';
            $profile->save();

            $configuration = App::make(ProcessorConfiguration::class);
            $configuration->configuration = [];
            $configuration->metadata = [];
            $configuration->rules = ['fileType' => '/csv/'];

            $configuration->profile()->associate($profile);
            $configuration->save();

            $processor = App::make(Processor::class)->firstOrNew(['unique_tag' => $tag]);

            if (!$processor->id) {
                $processor->name = "Example Goodtables";
                $processor->description = "Example showing cross-over with Goodtables";
                $processor->unique_tag = $tag;
                $processor->module = $path;
                $processor->content = $pData;
                $processor->save();
            }
            $configuration->processor()->associate($processor);
            $configuration->save();
        }

        $configuration = $profile->configurations[0];

        $validation->configuration()->associate($configuration);
        $validation->buildMetadata($metadata);
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

        return $validation->id;
    }
}
