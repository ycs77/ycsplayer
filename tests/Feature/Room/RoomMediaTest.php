<?php

use Database\Seeders\RoomSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\seed;

beforeEach(function () {
    seed(UserSeeder::class);
    seed(RoomSeeder::class);

    loginUser();
});

test('should visit room medias page', function () {
    $room = room('動漫觀影室');

    get("/rooms/{$room->hash_id}/medias")
        ->assertStatus(200);
});

test('should upload file', function () {
    Storage::fake('local');
    Storage::fake('public');

    Str::createRandomStringsUsing(fn () => 'mov_bbb');

    $room = room('動漫觀影室');

    $file = fakeFileFromPath('tests/fixtures/mov_bbb.mp3', 'mov_bbb.mp3');

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

    Storage::assertMissing('1/mov_bbb.mp3');

    post("/rooms/{$room->hash_id}/upload?".http_build_query($resumable), [
        'file' => $file,
    ])->assertJson([
        'success' => '檔案上傳成功',
    ]);

    Storage::assertExists('1/mov_bbb.mp3');
});

test('should remove a media', function () {
    Storage::fake();

    $room = room('動漫觀影室');

    $file = fakeFileFromPath('tests/fixtures/mov_bbb.mp3', 'mov_bbb.mp3');

    /** @var \Spatie\MediaLibrary\MediaCollections\Models\Media */
    $media = $room
        ->addMedia($file)
        ->usingName('Big Buck Bunny')
        ->usingFileName('mov_bbb.mp3')
        ->toMediaCollection();

    Storage::assertExists('1/mov_bbb.mp3');

    delete("/rooms/{$room->hash_id}/medias/{$media->uuid}");

    expect($room->getMedia()->firstWhere('name', 'Big Buck Bunny'))->toBeNull();

    Storage::assertMissing('1/mov_bbb.mp3');
});
