<?php

namespace App\Http\Controllers;

use App\Events\PlayerPaused;
use App\Events\PlayerPlayed;
use App\Events\PlayerSeeked;
use App\Player\PlayStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PlayerController extends Controller
{
    public function play(Request $request)
    {
        $request->validate([
            'room_id' => ['required'],
            'timestamp' => ['required', 'numeric'],
            'current_time' => ['nullable', 'numeric'],
            'is_clicked_big_button' => ['required', 'boolean'],
        ]);

        $socketId = $request->header('X-Socket-Id');

        $roomId = $request->input('room_id');

        /** @var PlayStatus */
        $status = Cache::get('room:'.$roomId);
        $isFirst = false;

        if (! $status) {
            $status = new PlayStatus();
            $isFirst = true;
        }

        $status->isClickedBigButton = $request->input('is_clicked_big_button');
        $status->paused = false;

        if (is_null($status->currentTime)) {
            $status->currentTime = 0.0;
        }

        if (is_numeric($request->input('current_time'))) {
            $status->currentTime = $request->input('current_time');
        }

        if ($status->isClickedBigButton || $isFirst) {
            $status->timestamp = $request->input('timestamp');

            Cache::put('room:'.$roomId, $status, now()->addHours(12));
        }

        PlayerPlayed::broadcast($socketId, $roomId, $status, $isFirst);

        return response()->noContent();
    }

    public function pause(Request $request)
    {
        $request->validate([
            'room_id' => ['required'],
            'current_time' => ['required', 'numeric'],
        ]);

        $socketId = $request->header('X-Socket-Id');

        $roomId = $request->input('room_id');

        $status = new PlayStatus();
        $status->currentTime = $request->input('current_time');
        $status->paused = true;

        Cache::put('room:'.$roomId, $status, now()->addHours(12));

        PlayerPaused::broadcast($socketId, $roomId, $status);

        return response()->noContent();
    }

    public function seeked(Request $request)
    {
        $request->validate([
            'room_id' => ['required'],
            'timestamp' => ['required', 'numeric'],
            'current_time' => ['required', 'numeric'],
            'paused' => ['required', 'boolean'],
        ]);

        $socketId = $request->header('X-Socket-Id');

        $roomId = $request->input('room_id');

        $status = new PlayStatus();
        $status->timestamp = $request->input('timestamp');
        $status->currentTime = $request->input('current_time');
        $status->paused = $request->input('paused');

        Cache::put('room:'.$roomId, $status, now()->addHours(12));

        PlayerSeeked::broadcast($socketId, $roomId, $status)->toOthers();

        return response()->noContent();
    }

    public function timeUpdate(Request $request)
    {
        $request->validate([
            'room_id' => ['required'],
            'timestamp' => ['required', 'numeric'],
            'current_time' => ['required', 'numeric'],
            'paused' => ['required', 'boolean'],
        ]);

        $roomId = $request->input('room_id');

        $status = new PlayStatus();
        $status->timestamp = $request->input('timestamp');
        $status->currentTime = $request->input('current_time');
        $status->paused = $request->input('paused');

        Cache::put('room:'.$roomId, $status, now()->addHours(12));

        return response()->noContent();
    }

    public function end(Request $request)
    {
        $request->validate([
            'room_id' => ['required'],
        ]);

        $roomId = $request->input('room_id');

        Cache::delete('room:'.$roomId);

        return response()->noContent();
    }
}
