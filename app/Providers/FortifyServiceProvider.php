<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\PasswordlessLogin\Actions\RedirectToSendPasswordlessLoginEmailRoute;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Laravel\Fortify\Contracts\LogoutResponse;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->instance(LogoutResponse::class, new class implements LogoutResponse {
            public function toResponse($request)
            {
                return Inertia::location('/');
            }
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::loginView(function () {
            return Inertia::render('Auth/Login', [
                'passwordLess' => config('ycsplayer.password_less'),
            ]);
        });

        if (config('ycsplayer.password_less')) {
            Fortify::authenticateThrough(function (Request $request) {
                return [RedirectToSendPasswordlessLoginEmailRoute::class];
            });
        }

        Fortify::registerView(function () {
            return Inertia::render('Auth/Register', [
                'passwordLess' => config('ycsplayer.password_less'),
            ]);
        });

        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });
    }
}
