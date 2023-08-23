<?php

namespace App\Listeners;

use App\Broadcasting\Events\PusherChannelVacated;
use App\Enums\PlayerType;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;

class PlayerAllConnectionClosed
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(PusherChannelVacated $event): void
    {
        $channelNamespace = 'presence-player.';

        $channels = array_map(fn (PlayerType $type) => $channelNamespace.$type->value, PlayerType::cases());

        if (in_array($event->channel, $channels)) {
            $type = PlayerType::from(substr($event->channel, strlen($channelNamespace)));

            Cache::delete('room:'.$type->value);
        }
    }
}
