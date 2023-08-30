<?php

namespace App\PasswordlessLogin\Http\Controllers;

use App\PasswordlessLogin\SendRateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Fortify;

class PasswordlessLoginController extends PasswordlessController
{
    public function login(Request $request, SendRateLimiter $limiter)
    {
        $this->preparePasswordless($request);

        $user = $this->getUser();

        $guard = $user->guard_name ?? config('laravel-passwordless-login.user_guard');
        $rememberLogin = $user->should_remember_login ?? config('laravel-passwordless-login.remember_login');
        $redirectUrl = $user->redirect_url ?? ($request->redirect_to ?: config('laravel-passwordless-login.redirect_on_success'));

        Auth::guard($guard)->login($user, $rememberLogin);

        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        $limiter->clearOnSent($request, $user->{Fortify::username()});

        return $user->guard_name
            ? $user->onPasswordlessLoginSuccess($request)
            : redirect($redirectUrl);
    }
}
