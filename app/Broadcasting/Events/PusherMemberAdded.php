<?php

namespace App\Broadcasting\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PusherMemberAdded
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public string $channel,
        public int $time_ms,
        public string $user_id,
    ) {
        //
    }
}
