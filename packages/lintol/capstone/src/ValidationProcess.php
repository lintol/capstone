<?php

namespace Lintol\Capstone;

use Log;
use RuntimeException;
use Event;
use Carbon\Carbon;
use Thruway\ClientSession;
use Lintol\Capstone\Models\Validation;
use Lintol\Capstone\Models\Report;
use Lintol\Capstone\Events\ResultRetrievedEvent;

class ValidationProcess
{
    protected $validation;

    protected $clientSession;

    public function fromDataSession($serverId, $sessionId, ClientSession $session)
    {
        $validation = Validation::where('doorstep_server_id', '=', $serverId)
            ->where('doorstep_session_id', '=', $sessionId)
            ->first();

        if (!$validation) {
            return null;
        }

        return new self($validation, $session);
    }

    public function make($validationId, ClientSession $session)
    {
        $validation = Validation::find($validationId);

        if (!$validation) {
            throw RuntimeException(__("Validation ID not found"));
        }

        return new self($validation, $session);
    }

    public function __construct(Validation $validation = null, ClientSession $session = null)
    {
        $this->validation = $validation;
        $this->session = $session;
        $this->reportFactory = app()->make(Report::class);
    }

    public function beginValidation($serverId, $sessionId) {
        $this->validation->doorstep_server_id = $serverId;
        $this->validation->doorstep_session_id = $sessionId;
        $this->validation->requested_at = Carbon::now();
        $this->validation->completion_status = Validation::STATUS_RUNNING;
        $this->validation->save();
    }

    public function engage() {
        return $this->session->call('com.ltldoorstep.engage');
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

    public function sendProcessor() {
        $processor = $this->validation->processor;

        $future = $this->session->call(
            $this->makeUri(
                'processor.post',
                $this->validation->doorstep_server_id
            ),
            [
                $this->validation->doorstep_session_id,
                $processor->module,
                $processor->content
            ]
        );

        return $future;
    }

    public function sendData() {
        $data = $this->validation->data;

        $future = $this->session->call(
            $this->makeUri(
                'data.post',
                $this->validation->doorstep_server_id
            ),
            [
                $this->validation->doorstep_session_id,
                $data->filename,
                $data->content
            ]
        );

        return $future;
    }

    public function markInitiated() {
        $this->validation->initiated_at = Carbon::now();
        $this->validation->save();
    }

    public function getValidationId() {
        return $this->validation->id;
    }

    /**
     * Run the validation sequence.
     */
    public function run()
    {
        return $this->engage()
        ->then(
            function ($res) {
                $this->beginValidation($res[0][0], $res[0][1]);
                return $this->sendProcessor();
            },
            function ($error) {
                Log::info($error);
                throw RuntimeException($error);
            }
        )->then(
            function ($res) {
                return $this->sendData();
            },
            function ($error) {
                Log::info($error);
                throw RuntimeException($error);
            }
        )->then(
            function ($res) {
                $this->markInitiated();

                Log::info(__("Validation process initiated for ") . $this->validation->id);
            },
            function ($error) {
                Log::info($error);
                throw RuntimeException($error);
            }
        );
    }

    protected function getReport() {
        $uri = $this->makeUri(
            'report.get',
            $this->validation->doorstep_server_id
        );

        return $this->session->call(
            $uri,
            [$this->validation->doorstep_session_id]
        );
    }

    protected function outputReport($report) {
        $report = $this->reportFactory->make($report);

        $report->validation()->associate($this->validation);
        $this->validation->completed_at = Carbon::now();
        $this->validation->save();
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
                throw RuntimeException($error);
            }
        )
        ->done(function ($res) {
            Log::info("Completed: " . $this->validation->id);
            Event::fire(new ResultRetrievedEvent($this->validation->id));
        });
    }
}
