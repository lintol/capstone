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
    public function handle(Validation $validationModel, ValidationProcess $validationProcess)
    {
        $validationId = $this->validationId;

        Log::info(__("Job running for validation ") . $validationId);

        $client = new Client('realm1');
        $client->addTransportProvider(new PawlTransportProvider(''));
        $client->setAttemptRetry(false);

        $validation = $validationModel->find($validationId);

        if (!$validation) {
            throw RuntimeException(__("Validation ID not found"));
        }

        $client->on('open', function (ClientSession $session) use ($validation, $client) {
            $process = $validationProcess->make($validation, $session);

            try {
                $session->call('com.ltldoorstep.engage')
                ->done(
                    function ($res) use ($process) {
                        $process->beginValidation($res[0][0], $res[0][1]);
                        return $process->sendProcessor();
                    },
                    function ($error) {
                        $session->close();
                        throw RuntimeException($error);
                    }
                )->then(
                    function ($res) use ($process) {
                        return $process->sendData();
                    },
                    function ($error) {
                        throw RuntimeException($error);
                    }
                )->then(
                    function ($res) use ($session, $process) {
                        $process->markInitiated();
                        Log::info(__("Validation process initiated for ") . $validation->id);
                    },
                    function ($error) use ($session) {
                        throw RuntimeException($error);
                    }
                );

                $session->close();
            } catch (Exception $e) {
                $session->close();
                Log::error($error);
            }
        });

        $client->start();

        Log::info(__("Client exited"));
    }

    protected function makeUri($endpoint, $serverId) {
        return 'com.ltldoorstep.' . $serverId . '.' . $endpoint;
    }
}
