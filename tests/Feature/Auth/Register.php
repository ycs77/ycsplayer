<?php

use function Pest\Laravel\get;
use function Pest\Laravel\post;

test('should visit register page', function () {
    get('/register')->assertStatus(200);
});

test('should register a new user with password', function () {
    post('/register', [
        'name' => 'Tomori',
        'email' => 'tomori@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertRedirect('/rooms');
});

test('should register a new user without password', function () {
    passwordless();

    post('/register', [
        'name' => 'Tomori',
        'email' => 'tomori@example.com',
        'password' => '',
        'password_confirmation' => '',
    ])->assertRedirect('/rooms');
});
