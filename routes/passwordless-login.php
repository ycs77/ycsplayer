<?php

use App\PasswordlessLogin\Http\Controllers\PasswordlessLoginController;
use App\PasswordlessLogin\Http\Controllers\PasswordlessLoginLinkController;
use Grosv\LaravelPasswordlessLogin\HandleAuthenticatedUsers;
use Illuminate\Support\Facades\Route;

Route::get('/login/send', [PasswordlessLoginLinkController::class, 'index'])->name('passwordless-login.send');
Route::post('/login/send', [PasswordlessLoginLinkController::class, 'store']);

Route::get(
    config('laravel-passwordless-login.login_route').'/{uid}',
    [PasswordlessLoginController::class, 'login']
)
    ->middleware(config('laravel-passwordless-login.middleware', [HandleAuthenticatedUsers::class]))
    ->name(config('laravel-passwordless-login.login_route_name'));
