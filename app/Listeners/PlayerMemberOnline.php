<?php

namespace App\Listeners;

use App\Broadcasting\Events\PusherMemberAdded;
use App\Events\RoomOnlineMembersUpdated;
use App\Models\Room;
use App\Room\RoomOnlineMembersRepository;
use Vinkla\Hashids\Facades\Hashids;

class PlayerMemberOnline
{
    /**
     * Create the event listener.
     */
    public function __construct(
        protected RoomOnlineMembersRepository $onlineMembers,
    ) {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(PusherMemberAdded $event): void
    {
        if (str_starts_with($event->channel, $channelNamespace = 'presence-player.')) {
            $roomHashId = substr($event->channel, strlen($channelNamespace));
            $roomId = current(Hashids::connection('rooms')->decode($roomHashId));

            if (Room::find($roomId, ['id'])) {
                $userHashId = Hashids::connection('users')->encode($event->user_id);

                $this->onlineMembers->online($roomHashId, $userHashId);

                RoomOnlineMembersUpdated::broadcast($roomHashId);
            }
        }
    }
}
