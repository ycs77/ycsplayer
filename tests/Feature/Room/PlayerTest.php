<?php

use App\Events\PlayerPaused;
use App\Events\PlayerPlayed;
use App\Events\PlayerSeeked;
use App\Player\PlayStatus;
use App\Player\PlayStatusCacheRepository;
use Database\Seeders\RoomSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Event;
use function Pest\Laravel\post;
use function Pest\Laravel\seed;

beforeEach(function () {
    seed(UserSeeder::class);
    seed(RoomSeeder::class);

    loginUser();
});

test('should first start play player', function () {
    Date::setTestNow('2023-01-01');

    Event::fake();

    $room = room('動漫觀影室');

    post('/player/play', [
        'room_id' => $room->hash_id,
        'timestamp' => Date::now()->timestamp,
        'current_time' => null,
        'is_clicked_big_button' => false,
    ], [
        'X-Socket-Id' => '0000.00000000',
    ])->assertNoContent();

    /** @var \App\Player\PlayStatus */
    $playStatus = app(PlayStatusCacheRepository::class)->get($room->hash_id, false);

    expect($playStatus->timestamp)->toBe(Date::now()->timestamp);
    expect($playStatus->currentTime)->toBe(0.0);
    expect($playStatus->isClickedBigButton)->toBeFalse();
    expect($playStatus->paused)->toBeFalse();

    Event::assertDispatched(PlayerPlayed::class);
});

test('should start play player in middle of progress', function () {
    Date::setTestNow('2023-01-01');

    Event::fake();

    $room = room('動漫觀影室');

    app(PlayStatusCacheRepository::class)
        ->store($room->hash_id, new PlayStatus([
            'timestamp' => Date::now()->timestamp,
            'current_time' => 6.26,
            'is_clicked_big_button' => true,
            'paused' => false,
        ]));

    post('/player/play', [
        'room_id' => $room->hash_id,
        'timestamp' => Date::now()->addMinute()->timestamp,
        'current_time' => null,
        'is_clicked_big_button' => false,
    ], [
        'X-Socket-Id' => '0000.00000000',
    ])->assertNoContent();

    /** @var \App\Player\PlayStatus */
    $playStatus = app(PlayStatusCacheRepository::class)->get($room->hash_id, false);

    expect($playStatus->timestamp)->toBe(Date::now()->timestamp);
    expect($playStatus->currentTime)->toBe(6.26);
    expect($playStatus->isClickedBigButton)->toBeFalse();
    expect($playStatus->paused)->toBeFalse();

    Event::assertDispatched(PlayerPlayed::class);
});

test('should continue play player', function () {
    Date::setTestNow('2023-01-01');

    Event::fake();

    $room = room('動漫觀影室');

    app(PlayStatusCacheRepository::class)
        ->store($room->hash_id, new PlayStatus([
            'current_time' => 6.26,
        ]));

    post('/player/play', [
        'room_id' => $room->hash_id,
        'timestamp' => Date::now()->timestamp,
        'current_time' => 7.38,
        'is_clicked_big_button' => true,
    ], [
        'X-Socket-Id' => '0000.00000000',
    ])->assertNoContent();

    /** @var \App\Player\PlayStatus */
    $playStatus = app(PlayStatusCacheRepository::class)->get($room->hash_id, false);

    expect($playStatus->timestamp)->toBe(Date::now()->timestamp);
    expect($playStatus->currentTime)->toBe(7.38);
    expect($playStatus->isClickedBigButton)->toBeTrue();
    expect($playStatus->paused)->toBeFalse();

    Event::assertDispatched(PlayerPlayed::class);
});

test('should pause player', function () {
    Date::setTestNow('2023-01-01');

    Event::fake();

    $room = room('動漫觀影室');

    app(PlayStatusCacheRepository::class)
        ->store($room->hash_id, new PlayStatus([
            'current_time' => 1.23,
        ]));

    post('/player/pause', [
        'room_id' => $room->hash_id,
        'current_time' => 6.26,
    ], [
        'X-Socket-Id' => '0000.00000000',
    ])->assertNoContent();

    /** @var \App\Player\PlayStatus */
    $playStatus = app(PlayStatusCacheRepository::class)->get($room->hash_id, false);

    expect($playStatus->currentTime)->toBe(6.26);

    Event::assertDispatched(PlayerPaused::class);
});

test('should seeked player', function () {
    Date::setTestNow('2023-01-01');

    Event::fake();

    $room = room('動漫觀影室');

    app(PlayStatusCacheRepository::class)
        ->store($room->hash_id, new PlayStatus([
            'current_time' => 30.51,
        ]));

    post('/player/seeked', [
        'room_id' => $room->hash_id,
        'timestamp' => Date::now()->timestamp,
        'current_time' => 35.51,
        'paused' => false,
    ], [
        'X-Socket-Id' => '0000.00000000',
    ])->assertNoContent();

    /** @var \App\Player\PlayStatus */
    $playStatus = app(PlayStatusCacheRepository::class)->get($room->hash_id, false);

    expect($playStatus->timestamp)->toBe(Date::now()->timestamp);
    expect($playStatus->currentTime)->toBe(35.51);
    expect($playStatus->paused)->toBeFalse();

    Event::assertDispatched(PlayerSeeked::class);
});

test('should update-time player', function () {
    Date::setTestNow('2023-01-01');

    $room = room('動漫觀影室');

    app(PlayStatusCacheRepository::class)
        ->store($room->hash_id, new PlayStatus());

    post('/player/time-update', [
        'room_id' => $room->hash_id,
        'timestamp' => Date::now()->timestamp,
        'current_time' => 0,
        'paused' => false,
    ], [
        'X-Socket-Id' => '0000.00000000',
    ])->assertNoContent();

    /** @var \App\Player\PlayStatus */
    $playStatus = app(PlayStatusCacheRepository::class)->get($room->hash_id, false);

    expect($playStatus->timestamp)->toBe(Date::now()->timestamp);
    expect($playStatus->currentTime)->toBe(0.0);
    expect($playStatus->isClickedBigButton)->toBeTrue();
    expect($playStatus->paused)->toBeFalse();
});

test('should end player', function () {
    $room = room('動漫觀影室');

    post('/player/end', [
        'room_id' => $room->hash_id,
    ])->assertNoContent();

    /** @var \App\Player\PlayStatus */
    $playStatus = app(PlayStatusCacheRepository::class)->get($room->hash_id, false);

    expect($playStatus)->toBeNull();
});
