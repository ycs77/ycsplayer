<?php

use App\Providers\RouteServiceProvider;

use function Pest\Laravel\get;
use function Pest\Laravel\post;

test('should visit register page', function () {
    get('/register')
        ->assertSuccessful();
});

test('should register a new user with password', function () {
    post('/register', [
        'name' => 'Tomori',
        'email' => 'tomori@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertRedirect(RouteServiceProvider::HOME);
});

test('should register a new user without password', function () {
    passwordless();

    post('/register', [
        'name' => 'Tomori',
        'email' => 'tomori@example.com',
        'password' => '',
        'password_confirmation' => '',
    ])->assertRedirect(RouteServiceProvider::HOME);
});
