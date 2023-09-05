<?php

use App\Models\Room;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('player.{roomId}', function (User $user) {
    return [
        'id' => $user->id,
        'name' => $user->name,
    ];
});

Broadcast::channel('medias.{roomId}', function (User $user, string $roomId) {
    if ($room = Room::find(Room::decodeHashId($roomId))) {
        return $user->can('uploadMedias', $room);
    }
});
