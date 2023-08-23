<?php

use App\Enums\PlayerType;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('player.{type}', function (User $user, string $type) {
    if (PlayerType::tryFrom($type) !== null &&
        $user->email === 'yangchenshin77@gmail.com'
    ) {
        return [
            'id' => $user->id,
            'name' => $user->name,
        ];
    }
});
