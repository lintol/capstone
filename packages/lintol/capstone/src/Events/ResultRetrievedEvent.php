<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ResultRetrievedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $validationId;

    /**
     * Create a new event instance.
     *
     * @param $validationId ID of the validation that has been returned
     * @return void
     */
    public function __construct($validationId)
    {
        $this->validationId = $validationId;
    }
}
