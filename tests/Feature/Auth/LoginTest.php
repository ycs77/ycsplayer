<?php

use App\Providers\RouteServiceProvider;
use Database\Seeders\UserSeeder;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertAuthenticated;
use function Pest\Laravel\assertGuest;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\seed;

beforeEach(function () {
    seed(UserSeeder::class);
});

test('should visit login page', function () {
    get('/login')
        ->assertSuccessful();
});

test('should submit login form', function () {
    post('/login', [
        'email' => 'admin@example.com',
        'password' => 'password',
        'remember' => true,
    ])->assertRedirect(RouteServiceProvider::HOME);

    assertAuthenticated();
});

test('should user logout', function () {
    actingAs(user(email: 'admin@example.com'));

    post('/logout')
        ->assertRedirect('/login');

    assertGuest();
});
