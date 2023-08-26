<?php

namespace App\Listeners;

use App\Broadcasting\Events\PusherChannelVacated;
use App\Models\Room;
use App\Player\PlayStatusCacheRepository;
use App\Room\RoomOnlineMembersRepository;
use Vinkla\Hashids\Facades\Hashids;

class PlayerAllConnectionClosed
{
    /**
     * Create the event listener.
     */
    public function __construct(
        protected PlayStatusCacheRepository $statusCache,
        protected RoomOnlineMembersRepository $onlineMembers,
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
            $roomId = current(Hashids::connection('rooms')->decode($roomHashId));

            if (Room::find($roomId, ['id'])) {
                $this->statusCache->delete($roomHashId);
                $this->onlineMembers->clear($roomHashId);
            }
        }
    }
}
