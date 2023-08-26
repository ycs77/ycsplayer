<?php

namespace App\Player;

use Illuminate\Contracts\Session\Session;

class PlayerGuard
{
    public function __construct(
        protected Session $session,
    ) {
        //
    }

    public function roomId(): ?string
    {
        return $this->session->get($this->getName());
    }

    public function check(string $roomId)
    {
        return $this->roomId() === $roomId;
    }

    public function authorized(string $roomId): void
    {
        $this->session->put($this->getName(), $roomId);
    }

    public function getName(): string
    {
        return 'player_'.sha1(static::class);
    }
}
