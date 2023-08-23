<?php

namespace App\Http\Controllers;

use App\Enums\PlayerType;
use App\Events\PlayerPaused;
use App\Events\PlayerPlayed;
use App\Events\PlayerSeeked;
use App\Player\PlayStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rules\Enum;

class PlayerController extends Controller
{
    public function play(Request $request)
    {
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

        PlayerPlayed::broadcast($type, $status);

        return response()->noContent();
    }

    public function pause(Request $request)
    {
        $request->validate([
            'player_type' => ['required', new Enum(PlayerType::class)],
            'timestamp' => ['required', 'numeric'],
            'current_time' => ['required', 'numeric'],
        ]);

        $type = PlayerType::from($request->input('player_type'));

        $status = new PlayStatus();
        $status->currentTime = $request->input('current_time');

        Cache::put('room:'.$type->value, $status, now()->addHours(12));

        PlayerPaused::broadcast($type, $status);

        return response()->noContent();
    }

    public function seeked(Request $request)
    {
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

        PlayerSeeked::broadcast($type, $status)->toOthers();

        return response()->noContent();
    }

    public function timeUpdate(Request $request)
    {
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
    }

    public function end(Request $request)
    {
        $request->validate([
            'player_type' => ['required', new Enum(PlayerType::class)],
        ]);

        $type = PlayerType::from($request->input('player_type'));

        Cache::delete('room:'.$type->value);

        return response()->noContent();
    }
}
