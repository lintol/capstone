<?php

namespace Lintol\Capstone;

use Auth;
use Log;
use RuntimeException;
use Event;
use Carbon\Carbon;
use Thruway\ClientSession;
use Lintol\Capstone\Models\ValidationRun;
use Lintol\Capstone\Models\Report;
use Lintol\Capstone\Models\Profile;
use Lintol\Capstone\Jobs\ProcessDataJob;
use Lintol\Capstone\Events\ResultRetrievedEvent;

class ValidationProcess
{
    protected $run;

    protected $clientSession;

    public function fromDataSession($serverId, $sessionId, ClientSession $session)
    {
        $validation = ValidationRun::where('doorstep_server_id', '=', $serverId)
            ->where('doorstep_session_id', '=', $sessionId)
            ->first();

        if (!$validation) {
            return null;
        }

        return new self($validation, $session);
    }

    public static function launch($data)
    {
        $user = Auth::user();

        $profiles = app()->make(Profile::class)->match($data->settings);
        $runs = $profiles->map(function ($profile) use ($data, $user) {
            $run = app()->make(ValidationRun::class);

            $run->requested_at = Carbon::now();
            $run->dataResource()->associate($data);
            $run->profile()->associate($profile);
            if ($user) {
                $run->creator()->associate($user);
            }

            $settings = $data->settings;
            if (!$run->buildDefinition($settings)) {
                \Log::info(__("Definition not built"));
                return null;
            }

            $run->save();

            \Log::info(__("Definition built"));
            return $run;
        })
        ->filter();

        $runs->each(function ($run) {
            ProcessDataJob::dispatch($run->id);
        });
        return $runs;
    }

    public function make($validationId, ClientSession $session)
    {
        $validation = ValidationRun::find($validationId);

        if (!$validation) {
            throw new \RuntimeException(__("Validation ID not found"));
        }

        return new self($validation, $session);
    }

    public function __construct(ValidationRun $validation = null, ClientSession $session = null)
    {
        $this->run = $validation;
        $this->session = $session;
        $this->reportFactory = app()->make(Report::class);
    }

    public function beginValidation($serverId, $sessionId)
    {
        $this->run->doorstep_server_id = $serverId;
        $this->run->doorstep_session_id = $sessionId;
        $this->run->initiated_at = Carbon::now();
        $this->run->completion_status = ValidationRun::STATUS_RUNNING;
        $this->run->save();
    }

    public function engage()
    {
        $call = $this->session->call('com.ltldoorstep.engage');
        return $call;
    }

    /**
     * Create WAMP URI for linking to a specific end-point within a server.
     *
     * @param $endpoint
     * @param $serverId
     * @return string
     */
    protected function makeUri($endpoint, $serverId)
    {
        return 'com.ltldoorstep.' . $serverId . '.' . $endpoint;
    }

    public function sendProcessor()
    {
        $configuration = $this->run->profile->configurations[0];
        $processor = $configuration->processor;
        $definition = $this->run->doorstep_definition;

        $future = $this->session->call(
            $this->makeUri(
                'processor.post',
                $this->run->doorstep_server_id
            ),
            [
                $this->run->doorstep_session_id,
                [$processor->module => $processor->content],
                $definition
            ]
        );

        return $future;
    }

    public function sendData()
    {
        $data = $this->run->dataResource;

        $future = $this->session->call(
            $this->makeUri(
                'data.post',
                $this->run->doorstep_server_id
            ),
            [
                $this->run->doorstep_session_id,
                $data->filename,
                $data->content
            ]
        );

        return $future;
    }

    public function markInitiated()
    {
        $this->run->initiated_at = Carbon::now();
        $this->run->save();
    }

    public function getValidationId()
    {
        return $this->run->id;
    }

    /**
     * Run the validation sequence.
     */
    public function run()
    {
        \Log::info('running...');
        return $this->engage()
        ->then(
            function ($res) {
                \Log::info('engaged...');
                $this->beginValidation($res[0][0], $res[0][1]);
                return $this->sendProcessor();
            },
            function ($error) {
                Log::info($error);
                throw new \RuntimeException($error);
            }
        )->then(
            function ($res) {
                \Log::info('sending data...');
                return $this->sendData();
            },
            function ($error) {
                Log::info($error);
                throw new \RuntimeException($error);
            }
        )->then(
            function ($res) {
                $this->markInitiated();

                Log::info(__("Validation process initiated for ") . $this->run->id);
            },
            function ($error) {
                Log::info($error);
                throw new \RuntimeException($error);
            }
        );
    }

    protected function getReport()
    {
        $uri = $this->makeUri(
            'report.get',
            $this->run->doorstep_server_id
        );

        return $this->session->call(
            $uri,
            [$this->run->doorstep_session_id]
        );
    }

    protected function outputReport($report)
    {
        $report = $this->reportFactory->make($report, $this->run);

        $this->run->markCompleted();

        $report->save();
    }

    /**
     * Run the output sequence.
     */
    public function retrieve()
    {
        $this->getReport()
        ->then(
            function ($res) {
                return $this->outputReport($res);
            },
            function ($error) {
                Log::info($error);
                throw new \RuntimeException($error);
            }
        )
        ->done(function ($res) {
            Log::info("Completed: " . $this->run->id);
            Event::fire(new ResultRetrievedEvent($this->run->id));
        });
    }
}
