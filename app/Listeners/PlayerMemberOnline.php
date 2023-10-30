<?php

namespace App\Listeners;

use App\Broadcasting\Events\PusherMemberAdded;
use App\Events\RoomOnlineMembersUpdated;
use App\Models\Room;
use App\Models\User;
use App\Room\RoomOnlineMembersCacheRepository;

class PlayerMemberOnline
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
    public function handle(PusherMemberAdded $event): void
    {
        if (str_starts_with($event->channel, $channelNamespace = 'presence-player.')) {
            $roomHashId = substr($event->channel, strlen($channelNamespace));
            $roomId = Room::decodeHashId($roomHashId);

            if (Room::find($roomId, ['id'])) {
                $userHashId = User::encodeHashId($event->user_id);

                $this->onlineMembers->online($roomHashId, $userHashId);

                RoomOnlineMembersUpdated::broadcast($roomHashId);
            }
        }
    }
}
