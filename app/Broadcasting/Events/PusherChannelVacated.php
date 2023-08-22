<?php

namespace App\Broadcasting\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PusherChannelVacated
{
    use Dispatchable, SerializesModels;

    public string $channel;
    public int $time_ms;

    public function __construct(string $channel, int $time_ms)
    {
        $this->channel = $channel;
        $this->time_ms = $time_ms;
    }
}
