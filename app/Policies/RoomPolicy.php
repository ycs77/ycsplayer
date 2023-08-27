<?php

namespace App\Policies;

use App\Models\Room;
use App\Models\User;

class RoomPolicy
{
    public function view(User $user, Room $room): bool
    {
        return $room->isMember($user);
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function operatePlaylistItem(User $user, Room $room): bool
    {
        return $user->can("rooms.{$room->id}.operate-playlist-item");
    }

    public function inviteMember(User $user, Room $room): bool
    {
        return $user->can("rooms.{$room->id}.invite-member");
    }

    public function removeMember(User $user, Room $room): bool
    {
        return $user->can("rooms.{$room->id}.remove-member");
    }

    public function uploadMedias(User $user, Room $room): bool
    {
        return $user->can("rooms.{$room->id}.upload-medias");
    }

    public function settings(User $user, Room $room): bool
    {
        return $user->can("rooms.{$room->id}.settings");
    }

    public function delete(User $user, Room $room): bool
    {
        return false;
    }
}
