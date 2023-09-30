<?php

use App\Events\RoomMediaConverted;
use App\Events\RoomMediaCreated;
use App\Events\RoomMediaRemoved;
use App\Jobs\AddRoomMediaFile;
use App\Models\QueueRoomFile;
use Database\Seeders\RoomSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Testing\Fluent\AssertableJson;
use function Pest\Laravel\delete;
use function Pest\Laravel\post;
use function Pest\Laravel\seed;

beforeEach(function () {
    seed(UserSeeder::class);
    seed(RoomSeeder::class);

    loginUser();
});

test('should upload file', function () {
    /** @var \Illuminate\Filesystem\FilesystemAdapter */
    $localDisk = Storage::fake('local');

    Queue::fake();

    Str::createRandomStringsUsing(fn () => 'mov_bbb');

    $room = room('動漫觀影室');

    $file = fakeFileFromPath('tests/Feature/Room/fixtures/mov_bbb.mp3');

    $resumable = [
        'resumableChunkNumber' => '1',
        'resumableChunkSize' => '5242880',
        'resumableCurrentChunkSize' => '160916',
        'resumableTotalSize' => '160916',
        'resumableType' => 'audio%2Fmpeg',
        'resumableIdentifier' => '160916-mov_bbbmp3',
        'resumableFilename' => 'mov_bbb.mp3',
        'resumableRelativePath' => 'mov_bbb.mp3',
        'resumableTotalChunks' => '1',
        '_token' => csrf_token(),
    ];

    post("/rooms/{$room->hash_id}/upload?".http_build_query($resumable), [
        'file' => $file,
    ])->assertJson(fn (AssertableJson $json) => $json->has('success'));

    /** @var \App\Models\QueueRoomFile */
    $queueRoomFile = QueueRoomFile::sole();
    expect($queueRoomFile->name)->toBe('mov_bbb');
    expect($queueRoomFile->path)->toBe('medias/mov_bbb.mp3');
    expect($queueRoomFile->disk)->toBe('local');

    $localDisk->assertExists('medias/mov_bbb.mp3');

    Queue::assertPushed(AddRoomMediaFile::class);
});

test('should convert media files', function () {
    /** @var \Illuminate\Filesystem\FilesystemAdapter */
    $localDisk = Storage::fake('local');

    /** @var \Illuminate\Filesystem\FilesystemAdapter */
    $publicDisk = Storage::fake('public');

    Event::fake();

    $room = room('動漫觀影室');

    $file = fakeFileFromPath('tests/Feature/Room/fixtures/mov_bbb.mp3');

    $queueFile = QueueRoomFile::create([
        'name' => 'mov_bbb',
        'path' => $file->storeAs('medias', 'mov_bbb.mp3', ['disk' => 'local']),
        'disk' => 'local',
        'expired_at' => now()->addHour(),
    ]);

    AddRoomMediaFile::dispatch($room, $queueFile);

    $localDisk->assertMissing('medias/mov_bbb.mp3');
    $publicDisk->assertExists('1/mov_bbb.mp3');

    Event::assertDispatched(RoomMediaCreated::class);
    Event::assertDispatched(RoomMediaConverted::class);
});

test('should remove a media', function () {
    Storage::fake();

    Event::fake([RoomMediaRemoved::class]);

    $room = room('動漫觀影室');

    $file = fakeFileFromPath('tests/Feature/Room/fixtures/mov_bbb.mp3');

    /** @var \Spatie\MediaLibrary\MediaCollections\Models\Media */
    $media = $room
        ->addMedia($file)
        ->usingName('mov_bbb')
        ->usingFileName('mov_bbb.mp3')
        ->toMediaCollection();

    Storage::assertExists('1/mov_bbb.mp3');

    delete("/rooms/{$room->hash_id}/medias/{$media->uuid}")
        ->assertSuccessful();

    expect($room->getMedia()->firstWhere('name', 'mov_bbb'))->toBeNull();

    Storage::assertMissing('1/mov_bbb.mp3');

    Event::assertDispatched(RoomMediaRemoved::class);
});
