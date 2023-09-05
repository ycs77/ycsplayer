<?php

use App\Enums\PlayerType;
use App\Events\PlayerlistItemClicked;
use App\Events\PlayerlistItemNexted;
use App\Events\PlayerlistItemRemoved;
use Database\Seeders\RoomSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use function Pest\Laravel\delete;
use function Pest\Laravel\post;
use function Pest\Laravel\seed;

beforeEach(function () {
    seed(UserSeeder::class);
    seed(RoomSeeder::class);

    loginUser();
});

test('should add video playlist item', function () {
    Storage::fake();

    $room = room('動漫觀影室');

    $file = fakeFileFromPath('tests/fixtures/mov_bbb.mp4');

    /** @var \Spatie\MediaLibrary\MediaCollections\Models\Media */
    $media = $room
        ->addMedia($file)
        ->usingName('Big Buck Bunny')
        ->usingFileName('mov_bbb.mp4')
        ->toMediaCollection();

    post("/rooms/{$room->hash_id}/playlist", [
        'type' => PlayerType::Video->value,
        'title' => 'Big Buck Bunny',
        'url' => null,
        'media_id' => $media->uuid,
    ]);

    $item = $room->playlist_items()->latest('id')->first();
    expect($item->type)->toBe(PlayerType::Video);
    expect($item->title)->toBe('Big Buck Bunny');
    expect($item->url)->toContain('/storage/1/mov_bbb.mp4');

    Storage::assertExists('1/mov_bbb.mp4');
});

test('should add audio playlist item', function () {
    Storage::fake();

    $room = room('動漫觀影室');

    $file = fakeFileFromPath('tests/fixtures/mov_bbb.mp3');

    /** @var \Spatie\MediaLibrary\MediaCollections\Models\Media */
    $media = $room
        ->addMedia($file)
        ->usingName('Big Buck Bunny')
        ->usingFileName('mov_bbb.mp3')
        ->toMediaCollection();

    post("/rooms/{$room->hash_id}/playlist", [
        'type' => PlayerType::Audio->value,
        'title' => 'Big Buck Bunny',
        'url' => null,
        'media_id' => $media->uuid,
    ]);

    $item = $room->playlist_items()->latest('id')->first();
    expect($item->type)->toBe(PlayerType::Audio);
    expect($item->title)->toBe('Big Buck Bunny');
    expect($item->url)->toContain('/storage/1/mov_bbb.mp3');

    Storage::assertExists('1/mov_bbb.mp3');
});

test('should add youtube playlist item', function () {
    $room = room('動漫觀影室');

    post("/rooms/{$room->hash_id}/playlist", [
        'type' => PlayerType::YouTube->value,
        'title' => '迷星叫',
        'url' => 'https://www.youtube.com/watch?v=B8k6JtF6WrU',
        'media_id' => null,
    ]);

    $item = $room->playlist_items()->latest('id')->first();
    expect($item->type)->toBe(PlayerType::YouTube);
    expect($item->title)->toBe('迷星叫');
    expect($item->url)->toBe('https://www.youtube.com/watch?v=B8k6JtF6WrU');
});

test('should click playlist item', function () {
    Event::fake();

    $room = room('動漫觀影室');
    $item = playlist($room, '星座になれたら');

    post("/rooms/{$room->hash_id}/playlist/{$item->hash_id}");

    $room->refresh();

    expect($room->current_playing_id)->toBe($item->id);

    Event::assertDispatched(PlayerlistItemClicked::class);
});

test('should remove playlist item is current playing', function () {
    Event::fake();

    $room = room('動漫觀影室');
    $item = playlist($room, '星座になれたら');
    $nextItem = playlist($room, 'spiral');

    $room->update(['current_playing_id' => $item->id]);

    delete("/rooms/{$room->hash_id}/playlist/{$item->hash_id}");

    $room->refresh();

    expect($room->current_playing_id)->toBe($nextItem->id);

    Event::assertDispatched(PlayerlistItemNexted::class);
});

test('should remove playlist item is not current playing', function () {
    Event::fake();

    $room = room('動漫觀影室');
    $item = playlist($room, '春日影');
    $currentItem = playlist($room, '星座になれたら');

    $room->update(['current_playing_id' => $currentItem->id]);

    delete("/rooms/{$room->hash_id}/playlist/{$item->hash_id}");

    $room->refresh();

    expect($room->current_playing_id)->toBe($currentItem->id);
    expect(playlist($room, '春日影'))->toBeNull();

    Event::assertDispatched(PlayerlistItemRemoved::class);
});

test('should move to next playlist item', function () {
    Event::fake();

    $room = room('動漫觀影室');
    $currentItem = playlist($room, '星座になれたら');
    $nextItem = playlist($room, 'spiral');

    $room->update(['current_playing_id' => $currentItem->id]);

    post("/rooms/{$room->hash_id}/next", [
        'current_playing_id' => $currentItem->hash_id,
    ]);

    $room->refresh();

    expect($room->current_playing_id)->toBe($nextItem->id);

    Event::assertDispatched(PlayerlistItemNexted::class);
});
