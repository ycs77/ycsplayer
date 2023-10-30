<?php

namespace App\Listeners;

use App\Broadcasting\Events\PusherChannelVacated;
use App\Models\Room;
use App\Room\RoomOnlineMembersCacheRepository;

class PlayerAllConnectionClosed
{
    /**
     * Create the event listener.
     */
    public function __construct(
        protected RoomOnlineMembersCacheRepository $onlineMembers,
    ) {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(PusherChannelVacated $event): void
    {
        if (str_starts_with($event->channel, $channelNamespace = 'presence-player.')) {
            $roomHashId = substr($event->channel, strlen($channelNamespace));
            $roomId = Room::decodeHashId($roomHashId);

            if (Room::find($roomId, ['id'])) {
                $this->onlineMembers->clear($roomHashId);
            }
        }
    }
}
