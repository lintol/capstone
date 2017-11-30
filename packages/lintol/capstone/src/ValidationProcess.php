<?php

namespace Lintol\Capstone;

use Carbon\Carbon;
use Thruway\ClientSession;
use Lintol\Capstone\Models\Validation;

class ValidationProcess
{
    protected $validation;

    protected $clientSession;

    public function make(Validation $validation, ClientSession $session)
    {
        return new self($validation, $session);
    }

    public function __construct(Validation $validation, ClientSession $session)
    {
        $this->validation = $validation;
        $this->session = $session;
    }

    public function beginValidation($serverId, $sessionId) {
        $this->validation->doorstep_server_id = $serverId;
        $this->validation->doorstep_session_id = $sessionId;
        $this->validation->requested_at = Carbon::now();
        $this->validation->completion_status = Validation::STATUS_RUNNING;
        $this->validation->save();
    }

    public function sendProcessor() {
        $processor = $this->validation->processor;

        $future = $this->session->call(
            $this->makeUri(
                'processor.post',
                $validation->doorstep_server_id
            ),
            [
                $validation->doorstep_session_id,
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
                $validation->doorstep_server_id
            ),
            [
                $validation->doorstep_session_id,
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
}
