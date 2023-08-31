<?php

namespace App\Flash;

use Illuminate\Session\Store as Session;

class Flash
{
    public function __construct(
        public Session $session,
    ) {
        //
    }

    public function success(string $message): void
    {
        $this->session->flash('success', $message);
    }

    public function error(string $message): void
    {
        $this->session->flash('error', $message);
    }
}
