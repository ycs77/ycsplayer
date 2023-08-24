<?php

namespace App\Broadcasting\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PusherWebhookReceived
{
    use Dispatchable, SerializesModels;

    public int $time_ms;

    public array $events;

    public function __construct(int $time_ms, array $events)
    {
        $this->time_ms = $time_ms;
        $this->events = $events;
    }
}
