<?php

namespace App\PasswordlessLogin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\PasswordlessLogin\Notifications\SendPasswordlessLoginLink;
use App\PasswordlessLogin\SendRateLimiter;
use Grosv\LaravelPasswordlessLogin\PasswordlessLogin;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use Illuminate\Session\Store as Session;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Laravel\Fortify\Fortify;
use Symfony\Component\HttpFoundation\Response;

class PasswordlessLoginLinkController extends Controller
{
    protected UserProvider $users;

    public function __construct(
        AuthManager $auth,
        protected Session $session,
        protected SendRateLimiter $limiter,
    ) {
        $this->users = $auth->createUserProvider($this->userProviderName());
    }

    public function index()
    {
        if ($email = $this->session->get('email')) {
            return Inertia::render('Auth/PasswordlessLogin', [
                'email' => $email,
                'seconds' => $this->session->get('seconds', 0),
            ]);
        }

        return redirect()->route('login');
    }

    public function store(Request $request)
    {
        $request->validate([Fortify::email() => 'required|email']);

        $this->session->flash(Fortify::email(), $request->input(Fortify::email()));
        $this->session->flash('seconds', $this->limiter->availableIn($request));

        if ($this->limiter->tooManyAttempts($request)) {
            throw ValidationException::withMessages([
                Fortify::email() => sprintf(
                    '請等候 %d 秒後再次嘗試重新發送 E-mail', $this->limiter->availableIn($request)
                ),
            ])->status(Response::HTTP_TOO_MANY_REQUESTS);
        }

        /** @var \App\Models\User|null */
        $user = $this->users->retrieveByCredentials($request->only(Fortify::email()));

        if ($user) {
            $url = PasswordlessLogin::forUser($user)->generate();

            $user->notify(new SendPasswordlessLoginLink($url));

            $this->limiter->increment($request);

            return redirect()
                ->route('passwordless-login.send')
                ->with('seconds', $this->limiter->availableIn($request));
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
