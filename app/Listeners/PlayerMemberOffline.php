<?php

namespace App\Listeners;

use App\Broadcasting\Events\PusherMemberRemoved;
use App\Events\RoomOnlineMembersUpdated;
use App\Models\Room;
use App\Room\RoomOnlineMembersCacheRepository;
use Vinkla\Hashids\Facades\Hashids;

class PlayerMemberOffline
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
    public function handle(PusherMemberRemoved $event): void
    {
        if (str_starts_with($event->channel, $channelNamespace = 'presence-player.')) {
            $roomHashId = substr($event->channel, strlen($channelNamespace));
            $roomId = current(Hashids::connection('rooms')->decode($roomHashId));

            if (Room::find($roomId, ['id'])) {
                $userHashId = Hashids::connection('users')->encode($event->user_id);

                $this->onlineMembers->offline($roomHashId, $userHashId);

                RoomOnlineMembersUpdated::broadcast($roomHashId);
            }
        }
    }
}
