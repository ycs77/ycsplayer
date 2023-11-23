<?php

use App\Http\Middleware\BlockRobots;
use App\PasswordlessLogin\Http\Controllers\PasswordlessDestroyUserController;
use App\PasswordlessLogin\Http\Controllers\PasswordlessDestroyUserLinkController;
use App\PasswordlessLogin\Http\Controllers\PasswordlessLoginController;
use App\PasswordlessLogin\Http\Controllers\PasswordlessLoginLinkController;
use Grosv\LaravelPasswordlessLogin\HandleAuthenticatedUsers;
use Illuminate\Support\Facades\Route;

// Password-less login
Route::get('/login/send', [PasswordlessLoginLinkController::class, 'index'])->name('passwordless-login.send');
Route::post('/login/send', [PasswordlessLoginLinkController::class, 'store']);

Route::get(
    config('laravel-passwordless-login.login_route').'/{uid}',
    [PasswordlessLoginController::class, 'login']
)
    ->middleware([BlockRobots::class, HandleAuthenticatedUsers::class])
    ->name(config('laravel-passwordless-login.login_route_name'));

// Password-less destroy user
Route::get('/user/destroy', [PasswordlessDestroyUserLinkController::class, 'index'])
    ->name('passwordless-destroy-user.send');

Route::get(
    '/passwordless-destroy-user/{uid}',
    [PasswordlessDestroyUserController::class, 'destroy']
)
    ->middleware(BlockRobots::class)
    ->name('passwordless-destroy-user.destroy');
