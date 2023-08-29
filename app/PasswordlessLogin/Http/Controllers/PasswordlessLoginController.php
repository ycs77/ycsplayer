<?php

namespace App\PasswordlessLogin\Http\Controllers;

use App\PasswordlessLogin\SendRateLimiter;
use Grosv\LaravelPasswordlessLogin\Exceptions\ExpiredSignatureException;
use Grosv\LaravelPasswordlessLogin\Exceptions\InvalidSignatureException;
use Grosv\LaravelPasswordlessLogin\LaravelPasswordlessLoginController;
use Grosv\LaravelPasswordlessLogin\PasswordlessLoginService;
use Illuminate\Http\Request;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Fortify;

class PasswordlessLoginController extends LaravelPasswordlessLoginController
{
    public function __construct(
        protected PasswordlessLoginService $passwordlessLoginService,
        protected UrlGenerator $urlGenerator,
        protected SendRateLimiter $limiter
    ) {
        //
    }

    public function login(Request $request)
    {
        if (! $this->urlGenerator->hasCorrectSignature($request) ||
            ($this->urlGenerator->signatureHasNotExpired($request) && ! $this->passwordlessLoginService->requestIsNew())
        ) {
            throw new InvalidSignatureException();
        } elseif (! $this->urlGenerator->signatureHasNotExpired($request)) {
            throw new ExpiredSignatureException();
        }

        $this->passwordlessLoginService->cacheRequest($request);

        /** @var mixed $user */
        $user = $this->passwordlessLoginService->user;

        $guard = $user->guard_name ?? config('laravel-passwordless-login.user_guard');
        $rememberLogin = $user->should_remember_login ?? config('laravel-passwordless-login.remember_login');
        $redirectUrl = $user->redirect_url ?? ($request->redirect_to ?: config('laravel-passwordless-login.redirect_on_success'));

        if (method_exists(Auth::guard($guard), 'login')) {
            Auth::guard($guard)->login($user, $rememberLogin);

            if ($request->hasSession()) {
                $request->session()->regenerate();
            }

            $this->limiter->clearOnSent($request, $user->{Fortify::username()});
        }

        return $user->guard_name
            ? $user->onPasswordlessLoginSuccess($request)
            : redirect($redirectUrl);
    }
}
