<?php

use App\Enums\PlayerType;
use App\Events\MediaPaused;
use App\Events\MediaPlayed;
use App\Events\MediaSeeked;
use App\Media\PlayStatus;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rules\Enum;
use Inertia\Inertia;

if (app()->environment('local') && ! app()->runningInConsole()) {
    Auth::login(User::first());
}

Route::redirect('/', '/rooms');

Route::inertia('/rooms', 'Room/Index');

Route::get('/rooms/{playerType}', function (PlayerType $playerType) {
    return Inertia::render('Room/Show', [
        'playerType' => $playerType,
    ]);
});

Route::post('/media/play', function (Request $request) {
    $request->validate([
        'player_type' => ['required', new Enum(PlayerType::class)],
        'timestamp' => ['nullable', 'numeric'],
        'is_started' => ['nullable', 'boolean'],
        'current_time' => ['nullable', 'numeric'],
    ]);

    $type = PlayerType::from($request->input('player_type'));

    /** @var PlayStatus */
    $status = Cache::get('room:'.$type->value, new PlayStatus());

    if (is_bool($isStarted = $request->input('is_started'))) {
        $status->isStarted = $isStarted;
    }

    // 如果觸發的當前播放器，正在點擊 bigPlayButton，
    // 就要校正播放時間。
    $timestamp = $request->input('timestamp');
    $currentTime = $request->input('current_time');
    if (! $isStarted &&
        is_numeric($timestamp) &&
        is_numeric($currentTime)
    ) {
        $status->timestamp = $timestamp;
        $status->currentTime = $currentTime;
    } else {
        Cache::put('room:'.$type->value, $status, now()->addHours(12));
    }

    MediaPlayed::broadcast($type, $status);

    return response()->noContent();
});

Route::post('/media/pause', function (Request $request) {
    $request->validate([
        'player_type' => ['required', new Enum(PlayerType::class)],
        'timestamp' => ['required', 'numeric'],
        'current_time' => ['required', 'numeric'],
    ]);

    $type = PlayerType::from($request->input('player_type'));

    $status = new PlayStatus();
    $status->currentTime = $request->input('current_time');

    Cache::put('room:'.$type->value, $status, now()->addHours(12));

    MediaPaused::broadcast($type, $status);

    return response()->noContent();
});

Route::post('/media/seeked', function (Request $request) {
    $request->validate([
        'player_type' => ['required', new Enum(PlayerType::class)],
        'timestamp' => ['required', 'numeric'],
        'current_time' => ['required', 'numeric'],
        'paused' => ['required', 'boolean'],
    ]);

    $type = PlayerType::from($request->input('player_type'));

    $status = new PlayStatus();
    $status->timestamp = $request->input('timestamp');
    $status->currentTime = $request->input('current_time');
    $status->paused = $request->input('paused');

    Cache::put('room:'.$type->value, $status, now()->addHours(12));

    MediaSeeked::broadcast($type, $status)->toOthers();

    return response()->noContent();
});

Route::post('/media/time-update', function (Request $request) {
    $request->validate([
        'player_type' => ['required', new Enum(PlayerType::class)],
        'timestamp' => ['required', 'numeric'],
        'current_time' => ['required', 'numeric'],
        'paused' => ['required', 'boolean'],
    ]);

    $type = PlayerType::from($request->input('player_type'));

    $status = new PlayStatus();
    $status->timestamp = $request->input('timestamp');
    $status->currentTime = $request->input('current_time');
    $status->paused = $request->input('paused');

    Cache::put('room:'.$type->value, $status, now()->addHours(12));

    return response()->noContent();
});

Route::post('/media/end', function (Request $request) {
    $request->validate([
        'player_type' => ['required', new Enum(PlayerType::class)],
    ]);

    $type = PlayerType::from($request->input('player_type'));

    Cache::delete('room:'.$type->value);

    return response()->noContent();
});
