<?php

namespace App\Providers;

use App\Contracts\CdnService;
use App\MediaLibrary\FileAdder;
use App\MediaLibrary\FileManipulator;
use App\MediaLibrary\Filesystem;
use App\Observers\MediaObserver;
use App\Services\Cdn\DOCdnService;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use Spatie\MediaLibrary\Conversions\FileManipulator as MediaLibraryFileManipulator;
use Spatie\MediaLibrary\MediaCollections\FileAdder as MediaLibraryFileAdder;
use Spatie\MediaLibrary\MediaCollections\Filesystem as MediaLibraryFilesystem;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if (! config('ycsplayer.mail') && config('ycsplayer.password_less')) {
            config()->set('ycsplayer.password_less', false);
        }

        // CDN
        $this->app->bind(CdnService::class, DOCdnService::class);

        // Media library
        $this->app->bind(MediaLibraryFileAdder::class, FileAdder::class);
        $this->app->bind(MediaLibraryFilesystem::class, Filesystem::class);
        $this->app->bind(MediaLibraryFileManipulator::class, FileManipulator::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Inertia::titleTemplate(fn ($title) => $title ? $title.' | ycsPlayer' : 'ycsPlayer');

        Media::observe(MediaObserver::class);
    }
}
