<?php

use App\Events\RoomNoteCanceled;
use App\Events\RoomNoteUpdated;
use App\Events\RoomNoteUpdating;
use App\Room\RoomNoteEditorCacheRepository;
use Database\Seeders\RoomSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Support\Facades\Event;

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

test('should start editing room note', function () {
    Event::fake();

    $room = room('動漫觀影室');

    /** @var \App\Room\RoomNoteEditorCacheRepository */
    $noteEditor = app(RoomNoteEditorCacheRepository::class);

    post("/rooms/{$room->hash_id}/note");

    expect($noteEditor->get($room->hash_id))->toBe([
        'id' => user(email: 'admin@example.com')->hash_id,
        'name' => 'Admin',
    ]);

    Event::assertDispatched(RoomNoteUpdating::class);
});

test('should update room note', function () {
    Event::fake();

    $room = room('動漫觀影室');

    /** @var \App\Room\RoomNoteEditorCacheRepository */
    $noteEditor = app(RoomNoteEditorCacheRepository::class);

    put("/rooms/{$room->hash_id}/note", [
        'note' => '更新了記事本內容XDD',
    ]);

    $room->refresh();

    expect($room->note)->toBe('更新了記事本內容XDD');
    expect($noteEditor->get($room->hash_id))->toBeNull();

    Event::assertDispatched(RoomNoteUpdated::class);
});

test('should cancel editing room note', function () {
    Event::fake();

    $room = room('動漫觀影室');

    /** @var \App\Room\RoomNoteEditorCacheRepository */
    $noteEditor = app(RoomNoteEditorCacheRepository::class);

    delete("/rooms/{$room->hash_id}/note");

    expect($noteEditor->get($room->hash_id))->toBeNull();

    Event::assertDispatched(RoomNoteCanceled::class);
});

test('should reset editing room note when user refresh page', function () {
    Event::fake();

    $room = room('動漫觀影室');

    /** @var \App\Room\RoomNoteEditorCacheRepository */
    $noteEditor = app(RoomNoteEditorCacheRepository::class);

    $noteEditor->start(
        $room->hash_id,
        user(email: 'admin@example.com')->hash_id,
        'Admin',
    );

    get("/rooms/{$room->hash_id}");

    expect($noteEditor->get($room->hash_id))->toBeNull();

    Event::assertDispatched(RoomNoteCanceled::class);
});
