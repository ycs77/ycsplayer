<?php

namespace App\PasswordlessLogin;

use Grosv\LaravelPasswordlessLogin\PasswordlessLoginManager;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class PasswordlessLoginProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('passwordless-login', function ($app) {
            return new PasswordlessLoginManager();
        });
    }

    public function boot()
    {
        Route::middleware('web')
            ->group(base_path('routes/passwordless-login.php'));
    }
}
