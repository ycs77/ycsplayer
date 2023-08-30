<?php

namespace App\PasswordlessLogin;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laravel\Fortify\LoginRateLimiter;

class SendRateLimiter extends LoginRateLimiter
{
    public function tooManyAttempts(Request $request)
    {
        return $this->limiter->tooManyAttempts($this->throttleKey($request), 1);
    }

    public function clearOnSent(Request $request, string $username)
    {
        $this->limiter->clear(
            Str::transliterate(Str::lower($username).'|'.$request->ip())
        );
    }
}
