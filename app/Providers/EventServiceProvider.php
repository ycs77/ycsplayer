<?php

namespace App\Providers;

use App\Broadcasting\Events\PusherChannelVacated;
use App\Broadcasting\Events\PusherMemberAdded;
use App\Broadcasting\Events\PusherMemberRemoved;
use App\Events\PlayerPaused;
use App\Events\PlayerPlayed;
use App\Listeners\PlayerAllConnectionClosed;
use App\Listeners\PlayerMemberOffline;
use App\Listeners\PlayerMemberOnline;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        PlayerPlayed::class => [],
        PlayerPaused::class => [],
        PusherChannelVacated::class => [
            PlayerAllConnectionClosed::class,
        ],
        PusherMemberAdded::class => [
            PlayerMemberOnline::class,
        ],
        PusherMemberRemoved::class => [
            PlayerMemberOffline::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
