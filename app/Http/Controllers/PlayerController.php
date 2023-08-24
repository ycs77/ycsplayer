<?php

namespace App\Http\Controllers;

use App\Events\PlayerPaused;
use App\Events\PlayerPlayed;
use App\Events\PlayerSeeked;
use App\Player\PlayStatus;
use App\Player\PlayStatusCacheRepository;
use Illuminate\Http\Request;

class PlayerController extends Controller
{
    public function __construct(
        protected PlayStatusCacheRepository $statusCache,
    ) {
        //
    }

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

        $status = $this->statusCache->get($roomId);
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

            $this->statusCache->store($roomId, $status);
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

        $this->statusCache->store($roomId, $status);

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

        $this->statusCache->store($roomId, $status);

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

        $this->statusCache->store($roomId, $status);

        return response()->noContent();
    }

    public function end(Request $request)
    {
        $request->validate([
            'room_id' => ['required'],
        ]);

        $roomId = $request->input('room_id');

        $this->statusCache->delete($roomId);

        return response()->noContent();
    }
}
