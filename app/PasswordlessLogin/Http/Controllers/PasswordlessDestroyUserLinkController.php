<?php

namespace App\PasswordlessLogin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Session\Store as Session;
use Inertia\Inertia;

class PasswordlessDestroyUserLinkController extends Controller
{
    protected UserProvider $users;

    public function __construct(
        AuthManager $auth,
        protected Session $session,
    ) {
        $this->users = $auth->createUserProvider($this->userProviderName());
    }

    public function index()
    {
        if ($email = $this->session->get('email')) {
            return Inertia::render('Auth/PasswordlessDestroyUser', [
                'email' => $email,
            ]);
        }

        return redirect()->route('user.settings');
    }

    protected function userProviderName(): string
    {
        return config('auth.guards.'.config('auth.defaults.guard').'.provider');
    }
}
