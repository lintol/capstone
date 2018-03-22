<?php

namespace Lintol\Capstone;

use Auth;
use Log;
use RuntimeException;
use Event;
use Carbon\Carbon;
use Throwable;
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

    protected $endedInException = false;

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
            $run->save();

            try {
                if ($user) {
                    $run->creator()->associate($user);
                }

                $run->dataResource()->associate($data);

                /* Saving here ensures this information is kept on error */
                $run->save();

                $run->profile()->associate($profile);

                $settings = $data->settings;
                if (!$run->buildDefinition($settings)) {
                    \Log::info(__("Definition not built"));
                    return null;
                }

                $run->save();

                \Log::info(__("Definition built"));
                return $run;
            } catch (Throwable $e) {
                $reportFactory = app()->make(Report::class);
                self::_recordException($reportFactory, $run, get_class($e) . ':' . $e->getCode(), $e->getMessage());
            }
        })
        ->filter();

        $runs->each(function ($run) {
            ProcessDataJob::dispatch($run->id);
        });
        return $runs;
    }

    protected function recordException($e)
    {
        if ($e instanceof \Thruway\Message\ErrorMessage) {
            $code = (string)($e) . ':' . $e->getErrorMsgCode();
            $message = json_encode([
                'details' => $e->getDetails(),
                'arguments' => $e->getArguments(),
                'keyword arguments' => $e->getArgumentsKw()
            ]);
        } elseif ($e instanceof \Throwable) {
            $code = get_class($e) . ':' . $e->getCode();
            $message = $e->getMessage();
        } else {
            $code = get_class($e);
            $message = (string)$e;
        }
        $this->recordExceptionString($code, $message);
    }

    protected function recordExceptionString(string $code, string $message)
    {
        Log::error($code);
        Log::error($message);
        if ($this->endedInException) {
            Log::info('... already recorded an exception for this validation process');
        } else {
            $this->endedInException = true;

            if ($this->run) {
                self::_recordException($this->reportFactory, $this->run, $code, $message);
            }
        }
    }

    protected static function _recordException(Report $reportFactory, ValidationRun $run, string $code, string $message)
    {
        \Log::error('-exception-');

        $content = [
            'valid' => false,
            'exception' => true,
            'error-count' => 1,
            'tables' => [
                [
                    'warnings' => [],
                    'informations' => [],
                    'errors' => [
                        'processor' => '(unidentified)',
                        'code' => $code,
                        'message' => $message,
                        'item' => [
                            'type' => 'Exception',
                            'location' => null,
                            'definition' => null
                        ]
                    ]
                ]
            ]
        ];
        $report = $reportFactory->make($content, $run, true);
        $run->markCompleted();

        $report->save();
        $run->save();
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
        $configurations = $this->run->profile->configurations;
        $processors = $configurations->pluck('processor');
        $definition = $this->run->doorstep_definition;

        $future = $this->session->call(
            $this->makeUri(
                'processor.post',
                $this->run->doorstep_server_id
            ),
            [
                $this->run->doorstep_session_id,
                $processors->pluck('content', 'module')->toArray(),
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
        try {
            $promise = $this->engage()
            ->then(
                function ($res) {
                    \Log::info('engaged...');
                    $this->beginValidation($res[0][0], $res[0][1]);
                    return $this->sendProcessor();
                },
                function ($error) {
                    Log::info(get_class($error));
                    Log::info($error);
                    $this->recordException($error);
                    throw new \RuntimeException($error);
                }
            )->then(
                function ($res) {
                    \Log::info('sending data...');
                    return $this->sendData();
                },
                function ($error) {
                    Log::info(get_class($error));
                    Log::info($error);
                    $this->recordException($error);
                    throw new \RuntimeException($error);
                }
            )->then(
                function ($res) {
                    $this->markInitiated();

                    Log::info(__("Validation process initiated for ") . $this->run->id);
                },
                function ($error) {
                    Log::info(get_class($error));
                    $this->recordException($error);
                    throw new \RuntimeException($error);
                }
            );
        } catch (Throwable $e) {
            Log::info(get_class($e));
            Log::error($e);
            $this->recordException($e);
            throw $e;
        }
        return $promise;
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
                $e = new \RuntimeException($error);
                $this->recordException($e);
                throw $e;
            }
        )
        ->done(function ($res) {
            Log::info("Completed: " . $this->run->id);
            Event::fire(new ResultRetrievedEvent($this->run->id));
        });
    }
}
