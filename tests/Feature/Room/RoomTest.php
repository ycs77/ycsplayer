<?php

use App\Enums\PlayerType;
use App\Enums\RoomType;
use Database\Seeders\RoomSeeder;
use Database\Seeders\UserSeeder;
use Inertia\Testing\AssertableInertia as Assert;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\put;
use function Pest\Laravel\seed;

beforeEach(function () {
    seed(UserSeeder::class);
    seed(RoomSeeder::class);

    loginUser();
});

test('should visit rooms page', function () {
    $videoRoom = room('動漫觀影室');
    $audioRoom = room('動漫音樂廳');

    get('/rooms')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Room/Index')
            ->has('rooms.data', 2)
            ->has('rooms.data.0', fn (Assert $page) => $page
                ->where('id', $videoRoom->hash_id)
                ->where('type', RoomType::Video->value)
                ->where('name', '動漫觀影室')
                ->etc()
            )
            ->has('rooms.data.1', fn (Assert $page) => $page
                ->where('id', $audioRoom->hash_id)
                ->where('type', RoomType::Audio->value)
                ->where('name', '動漫音樂廳')
                ->etc()
            )
        );
});

test('should visit room page with no playing', function () {
    $room = room('動漫觀影室');

    get("/rooms/{$room->hash_id}")
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Room/Show')
            ->has('room', fn (Assert $page) => $page
                ->where('id', $room->hash_id)
                ->where('type', RoomType::Video->value)
                ->where('name', '動漫觀影室')
                ->where('auto_play', false)
                ->where('auto_remove', true)
                ->where('debug', false)
                ->where('note', '記事本文字欄...')
            )
            ->where('currentPlaying', null)
            ->has('playlistItems', 4)
            ->has('members', 2)
            ->has('members.0', fn (Assert $page) => $page
                ->where('name', 'Admin')
                ->where('email', 'admin@example.com')
                ->where('role', 'admin')
                ->etc()
            )
            ->has('members.1', fn (Assert $page) => $page
                ->where('name', 'Soyo')
                ->where('email', 'soyo@example.com')
                ->where('role', 'user')
                ->etc()
            )
        );
});

test('should visit room page has current playing', function () {
    $room = room('動漫觀影室');
    $playlistItem = playlist($room, '春日影');

    $room->update([
        'current_playing_id' => $playlistItem->id,
    ]);

    get("/rooms/{$room->hash_id}")
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Room/Show')
            ->has('currentPlaying', fn (Assert $page) => $page
                ->where('type', PlayerType::YouTube->value)
                ->where('title', '春日影')
                ->where('url', 'https://www.youtube.com/watch?v=W8DCWI_Gc9c')
                ->etc()
            )
        );
});

test('should create a new room', function () {
    $response = post('/rooms', [
        'name' => '為什麼要開新房間?',
        'type' => RoomType::Video->value,
        'auto_play' => false,
        'auto_remove' => true,
    ]);

    $room = room('為什麼要開新房間?');
    $user = user(email: 'admin@example.com');

    $response->assertRedirect("/rooms/{$room->hash_id}");

    expect($room->name)->toBe('為什麼要開新房間?');
    expect($room->type)->toBe(RoomType::Video);
    expect($room->auto_play)->toBeFalse();
    expect($room->auto_remove)->toBeTrue();
    expect($room->isMember($user))->toBeTrue();
    expect($user->getRoleNames())->toContain("rooms.{$room->id}.admin");
});

test('should update room settings', function () {
    $room = room('動漫觀影室');

    put("/rooms/{$room->hash_id}", [
        'name' => '為什麼要改房間名字?',
        'type' => RoomType::Audio->value,
        'auto_play' => true,
        'auto_remove' => false,
        'debug' => false,
    ]);

    $room->refresh();

    expect($room->name)->toBe('為什麼要改房間名字?');
    expect($room->type)->toBe(RoomType::Audio);
    expect($room->auto_play)->toBeTrue();
    expect($room->auto_remove)->toBeFalse();
});

test('should destroy room', function () {
    $room = room('動漫觀影室');

    delete("/rooms/{$room->hash_id}")
        ->assertRedirect('/rooms');

    expect(room('動漫觀影室'))->toBeNull();
});
