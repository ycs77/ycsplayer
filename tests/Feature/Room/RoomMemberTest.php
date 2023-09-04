<?php

use Database\Seeders\RoomSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Support\Facades\Date;
use Illuminate\Testing\Fluent\AssertableJson;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\patch;
use function Pest\Laravel\post;
use function Pest\Laravel\seed;
use function Pest\Laravel\withoutMiddleware;

beforeEach(function () {
    seed(UserSeeder::class);
    seed(RoomSeeder::class);

    loginUser();
});

test('new member should join to room', function () {
    $user = loginUser('soyo@example.com');

    $room = room('動漫音樂廳');

    expect($room->isMember($user))->toBeFalse();

    // without `signed` middleware
    withoutMiddleware(\App\Http\Middleware\ValidateSignature::class);

    get("/rooms/{$room->hash_id}/join")
        ->assertRedirect("/rooms/{$room->hash_id}");

    expect($room->isMember($user))->toBeTrue();
});

test('should generate join link', function () {
    Date::setTestNow('2023-01-01');

    $room = room('動漫觀影室');

    post("/rooms/{$room->hash_id}/generate-join-link")
        ->assertJson([
            'join_link' => "http://localhost/rooms/{$room->hash_id}/join?expires=1672588800&signature=57ac69f616f730d22d04df4b361cd05aa0992134bc781de4f80c2ffa969fdda5",
        ]);
});

test('should invite new member to room', function () {
    $room = room('動漫觀影室');

    post("/rooms/{$room->hash_id}/invite", [
        'email' => 'soyo@example.com',
    ]);

    expect($room->isMember(user(email: 'soyo@example.com')))->toBeTrue();
});

test('should search a member by e-mail', function () {
    $room = room('動漫觀影室');

    post("/rooms/{$room->hash_id}/search-member", [
        'email' => 'soyo@example.com',
    ])->assertJson(fn (AssertableJson $json) => $json
        ->has('member', fn (AssertableJson $json) => $json
            ->where('name', 'Soyo')
            ->where('email', 'soyo@example.com')
            ->etc()
        )
    );
});

test('admin should change a member role to uploader', function () {
    $room = room('動漫觀影室');

    $member = user(email: 'soyo@example.com');

    patch("/rooms/{$room->hash_id}/members/{$member->hash_id}/role", [
        'role' => 'uploader',
    ]);

    $roles = $member->getRoleNames();
    expect($roles)->toHaveCount(1);
    expect($roles)->toContain('rooms.1.uploader');
});

test('member should leave out of room', function () {
    $user = loginUser('soyo@example.com');

    $room = room('動漫觀影室');

    delete("/rooms/{$room->hash_id}/members/{$user->hash_id}", [
        'email' => 'soyo@example.com',
    ])->assertRedirect('/rooms');

    expect($room->isMember($user))->toBeFalse();
});

test('admin should move a member out of room', function () {
    $room = room('動漫觀影室');

    $member = user(email: 'soyo@example.com');

    delete("/rooms/{$room->hash_id}/members/{$member->hash_id}", [
        'email' => 'soyo@example.com',
    ]);

    expect($room->isMember($member))->toBeFalse();
});
