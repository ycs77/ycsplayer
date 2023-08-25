<?php

namespace App\Providers;

use App\Observers\MediaObserver;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Inertia::titleTemplate(fn ($title) => $title ? $title.' | '.config('app.name') : config('app.name'));

        Media::observe(MediaObserver::class);
    }
}
