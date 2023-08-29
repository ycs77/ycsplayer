<?php

namespace App\PasswordlessLogin\Actions;

use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Fortify;

class RedirectToSendPasswordlessLoginEmailRoute
{
    protected UserProvider $users;

    public function __construct(AuthManager $auth)
    {
        $this->users = $auth->createUserProvider($this->userProviderName());
    }

    public function handle(Request $request, callable $next)
    {
        $user = $this->users->retrieveByCredentials($request->only(Fortify::email()));

        if ($user) {
            return redirect()
                ->route('passwordless-login.send')
                ->with('email', $user->email);
        }

        throw ValidationException::withMessages([
            Fortify::email() => 'E-mail 不正確',
        ]);
    }

    protected function userProviderName(): string
    {
        return config('auth.guards.'.config('auth.defaults.guard').'.provider');
    }
}
