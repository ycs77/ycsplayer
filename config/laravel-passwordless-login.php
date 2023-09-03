<?php

use App\Providers\RouteServiceProvider;
use Grosv\LaravelPasswordlessLogin\HandleAuthenticatedUsers;

return [

    'user_guard' => 'web',
    'remember_login' => env('LPL_REMEMBER_LOGIN', true),
    'login_route' => '/passwordless-login',
    'login_route_name' => 'passwordless-login.verify',
    'login_route_expires' => 30, // 30m
    'redirect_on_success' => RouteServiceProvider::HOME,
    'login_use_once' => true,
    'invalid_signature_message' => '當前登入連結可能是無效或過期了',
    'middleware' => ['web', HandleAuthenticatedUsers::class],

];
