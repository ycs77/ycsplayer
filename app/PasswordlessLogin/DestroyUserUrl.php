<?php

namespace App\PasswordlessLogin;

use Grosv\LaravelPasswordlessLogin\UserClass;
use Illuminate\Contracts\Auth\Authenticatable as User;
use Illuminate\Support\Facades\URL;

class DestroyUserUrl
{
    protected User $user;

    protected string $routeName;

    /** @var \DateTimeInterface|\DateInterval|int */
    protected $routeExpires;

    protected ?string $redirectUrl = null;

    public function __construct(User $user, $routeName = 'passwordless-destroy-user.destroy')
    {
        $this->user = $user;

        $this->routeExpires = now()->addMinutes(
            $this->user->login_route_expires_in ?? config('laravel-passwordless-login.login_route_expires')
        );

        $this->routeName = $routeName;
    }

    public static function forUser(User $user, $routeName = 'passwordless-destroy-user.destroy')
    {
        return new static($user, $routeName);
    }

    public function setRedirectUrl(string $redirectUrl)
    {
        $this->redirectUrl = $redirectUrl;
    }

    public function generate()
    {
        return URL::temporarySignedRoute($this->routeName, $this->routeExpires, [
            'uid' => $this->user->getAuthIdentifier(),
            'redirect_to' => $this->redirectUrl,
            'user_type' => UserClass::toSlug(get_class($this->user)),
        ]);
    }
}
