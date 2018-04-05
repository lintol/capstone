<?php

namespace Lintol\Capstone\Listeners;

use Log;
use Lintol\Capstone\WampConnection;
use Lintol\Capstone\Events\ResultRetrievedEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Lintol\Capstone\Models\ValidationRun;

class ResultRetrievedListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(WampConnection $wampConnection)
    {
        $this->wampConnection = $wampConnection;
    }

    /**
     * Handle the event.
     *
     * @param  ResultRetrievedEvent  $event
     * @return void
     */
    public function handle(ResultRetrievedEvent $event)
    {
        $validation = ValidationRun::findOrFail($event->validationId);

        if (!$validation) {
            throw RuntimeException(__("Validation Run ID not found"));
        }

        $this->wampConnection->execute(function ($session) use ($validation) {
            Log::info('Publishing com.ltlcapstone.validation.' . $validation->id . '.event_complete');

            echo 'Publishing com.ltlcapstone.validation.' . $validation->id . '.event_complete';
            $content = null;
            if ($validation->report) {
                $content = $validation->report->content;
            }

            return $session->publish(
                'com.ltlcapstone.validation.' . $validation->id . '.event_complete',
                [$content]
            );
        }, false);
    }
}
