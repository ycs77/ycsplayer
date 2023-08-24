<?php

namespace App\Listeners;

use App\Broadcasting\Events\PusherChannelVacated;
use App\Models\Room;
use App\Player\PlayStatusCacheRepository;

class PlayerAllConnectionClosed
{
    /**
     * Create the event listener.
     */
    public function __construct(
        protected PlayStatusCacheRepository $statusCache,
    ) {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(PusherChannelVacated $event): void
    {
        $channelNamespace = 'presence-player.';

        if (str_starts_with($event->channel, $channelNamespace)) {
            $roomId = substr($event->channel, strlen($channelNamespace));

            if (Room::find($roomId, ['id'])) {
                $this->statusCache->delete($roomId);
            }
        }
    }
}
