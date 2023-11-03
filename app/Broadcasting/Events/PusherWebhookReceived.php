<?php

namespace App\Broadcasting\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PusherWebhookReceived
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $time_ms,
        public array $events,
    ) {
        //
    }
}
