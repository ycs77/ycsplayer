<?php

use App\Models\Room;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;
use Vinkla\Hashids\Facades\Hashids;

Broadcast::channel('player.{roomId}', function (User $user) {
    return [
        'id' => $user->id,
        'name' => $user->name,
    ];
});

Broadcast::channel('medias.{roomId}', function (User $user, string $roomId) {
    if ($room = Room::find(current(Hashids::connection('rooms')->decode($roomId)))) {
        return $user->can('uploadMedias', $room);
    }
});
