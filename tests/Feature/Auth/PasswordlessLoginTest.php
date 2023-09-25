<?php

use App\PasswordlessLogin\Notifications\SendPasswordlessLoginLink;
use App\Providers\RouteServiceProvider;
use Database\Seeders\UserSeeder;
use Grosv\LaravelPasswordlessLogin\UserClass;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use function Pest\Laravel\assertAuthenticatedAs;
use function Pest\Laravel\assertGuest;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\seed;

beforeEach(function () {
    seed(UserSeeder::class);

    passwordless();
});

test('should submit password-less login form', function () {
    post('/login', [
        'email' => 'admin@example.com',
        'password' => 'password',
        'remember' => true,
    ])->assertRedirect('/login/send');

    assertGuest();

    expect(session('email'))->toBe('admin@example.com');
});

test('should send password-less login link email', function () {
    Notification::fake();

    $user = user(email: 'admin@example.com');

    post('/login/send', [
        'email' => 'admin@example.com',
    ])->assertRedirect('/login/send');

    Notification::assertSentTo($user, SendPasswordlessLoginLink::class);

    expect(session('email'))->toBe('admin@example.com');
    expect(session('seconds'))->toBe(60);
});

test('should vist password-less login link to login', function () {
    $user = user(email: 'admin@example.com');

    $url = URL::temporarySignedRoute('passwordless-login.verify', now()->addMinutes(30), [
        'uid' => $user->id,
        'redirect_to' => null,
        'user_type' => UserClass::toSlug(get_class($user)),
    ]);

    get($url)
        ->assertRedirect(RouteServiceProvider::HOME);

    assertAuthenticatedAs($user);
});
