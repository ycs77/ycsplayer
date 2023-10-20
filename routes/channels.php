<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('player.{roomId}', function (User $user) {
    return [
        'id' => $user->hash_id,
        'name' => $user->name,
    ];
});
