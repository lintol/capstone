<?php

namespace App\Listeners;

use App\Events\ResultRetrievedEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Lintol\Capstone\Models\Validation;

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
        $validation = Validation::find($event->validationId);

        if (!$validation) {
            throw RuntimeException(__("Validation ID not found"));
        }

        $this->wampConnection->execute(function ($session) use ($validation) {
            Log::info('Publishing com.ltlcapstone.validation.' . $validation->id . '.event_complete');

            return $session->publish(
                'com.ltlcapstone.validation.' . $validation->id . '.event_complete',
                [$validation->report]
            );
        });
    }
}
