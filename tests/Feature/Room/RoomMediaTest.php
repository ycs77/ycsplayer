<?php

use App\Events\RoomMediaConverted;
use App\Events\RoomMediaUploaded;
use App\Jobs\AddRoomMediaFile;
use App\Models\QueueRoomFile;
use Database\Seeders\RoomSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Testing\Fluent\AssertableJson;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

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

    Event::fake([RoomMediaUploaded::class]);

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
    $queueRoomFile = QueueRoomFile::first();
    expect($queueRoomFile->name)->toBe('mov_bbb');
    expect($queueRoomFile->path)->toBe('medias/mov_bbb.mp3');
    expect($queueRoomFile->disk)->toBe('local');

    $localDisk->assertExists('medias/mov_bbb.mp3');

    Event::assertDispatched(RoomMediaUploaded::class);

    Queue::assertPushed(AddRoomMediaFile::class);

    Str::createRandomStringsNormally();
});

test('QueueRoomFile should generate loading media model', function () {
    $room = room('動漫觀影室');

    /** @var \App\Models\QueueRoomFile */
    $queueFile = $room->queueFiles()->create([
        'name' => 'mov_bbb',
        'path' => 'medias/mov_bbb.mp3',
        'disk' => 'local',
        'expired_at' => now()->addHour(),
    ]);

    $loadingMedia = $queueFile->loadingMedia();

    expect($loadingMedia)->toBeInstanceOf(Media::class);
    expect($loadingMedia->name)->toBe('mov_bbb');
    expect($loadingMedia->file_name)->toBe('mov_bbb.mp3');
    expect($loadingMedia->converting)->toBeTrue();
});

test('should convert media files', function () {
    /** @var \Illuminate\Filesystem\FilesystemAdapter */
    $localDisk = Storage::fake('local');

    /** @var \Illuminate\Filesystem\FilesystemAdapter */
    $publicDisk = Storage::fake('public');

    Event::fake([RoomMediaConverted::class]);

    $room = room('動漫觀影室');

    $file = fakeFileFromPath('tests/Feature/Room/fixtures/mov_bbb.mp3');

    /** @var \App\Models\QueueRoomFile */
    $queueFile = $room->queueFiles()->create([
        'name' => 'mov_bbb',
        'path' => $file->storeAs('medias', 'mov_bbb.mp3', ['disk' => 'local']),
        'disk' => 'local',
        'expired_at' => now()->addHour(),
    ]);

    AddRoomMediaFile::dispatch($room, $queueFile);

    $localDisk->assertMissing('medias/mov_bbb.mp3');
    $publicDisk->assertExists('1/mov_bbb.mp3');

    Event::assertDispatched(RoomMediaConverted::class);
});

test('should remove a media', function () {
    Storage::fake();

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
});
