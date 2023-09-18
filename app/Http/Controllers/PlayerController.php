<?php

namespace App\Http\Controllers;

use App\Events\PlayerPaused;
use App\Events\PlayerPlayed;
use App\Events\PlayerSeeked;
use App\Models\Room;
use App\Player\PlayerGuard;
use App\Player\PlayStatus;
use App\Player\PlayStatusCacheRepository;
use Illuminate\Http\Request;

class PlayerController extends Controller
{
    public function __construct(
        protected PlayerGuard $playerGuard,
        protected PlayStatusCacheRepository $statusCache,
    ) {
        //
    }

    public function play(Request $request)
    {
        $request->validate([
            'room_id' => ['required', 'string', 'max:20'],
            'timestamp' => ['required', 'numeric'],
            'current_time' => ['nullable', 'numeric'],
            'is_clicked_big_button' => ['required', 'boolean'],
        ]);

        $roomId = $this->getRoomId($request);

        $socketId = $request->header('X-Socket-Id');

        $status = $this->statusCache->get($roomId);
        $isFirst = false;

        if (! $status) {
            $status = new PlayStatus();
            $isFirst = true;
        }

        $status->isClickedBigButton = $request->input('is_clicked_big_button');

        $paused = $status->paused;
        $status->paused = false;

        if (is_numeric($request->input('current_time'))) {
            $status->currentTime = $request->input('current_time');
        } elseif (is_null($status->currentTime)) {
            $status->currentTime = 0.0;
        }

        if (config('ycsplayer.debug')) {
            $status->log('player play', [
                'roomId' => $roomId,
                'mode' => 'play',
                'user' => auth()->user()->name,
            ]);
        }

        // 只有以下條件之一的才能儲存當前播放進度:
        // 1. 初次播放
        // 2. 已經點擊過開始播放鍵
        // 3. 當前是暫停狀態
        if ($isFirst || $status->isClickedBigButton || $paused) {
            $status->timestamp = $request->input('timestamp');

            $this->statusCache->store($roomId, $status);
        }

        PlayerPlayed::broadcast($socketId, $roomId, $status, $isFirst);

        return response()->noContent();
    }

    public function pause(Request $request)
    {
        $request->validate([
            'room_id' => ['required', 'string', 'max:12'],
            'timestamp' => ['required', 'numeric'],
            'current_time' => ['required', 'numeric'],
        ]);

        $roomId = $this->getRoomId($request);

        $socketId = $request->header('X-Socket-Id');

        $status = $this->statusCache->get($roomId) ?? new PlayStatus();
        $status->timestamp = $request->input('timestamp');
        $status->currentTime = $request->input('current_time');
        $status->paused = true;

        if (config('ycsplayer.debug')) {
            $status->log('player pause', [
                'roomId' => $roomId,
                'mode' => 'pause',
                'user' => auth()->user()->name,
            ]);
        }

        $this->statusCache->store($roomId, $status);

        PlayerPaused::broadcast($socketId, $roomId, $status);

        return response()->noContent();
    }

    public function seeked(Request $request)
    {
        $request->validate([
            'room_id' => ['required', 'string', 'max:12'],
            'timestamp' => ['required', 'numeric'],
            'current_time' => ['required', 'numeric'],
            'paused' => ['required', 'boolean'],
        ]);

        $roomId = $this->getRoomId($request);

        $socketId = $request->header('X-Socket-Id');

        $status = $this->statusCache->get($roomId) ?? new PlayStatus();
        $status->timestamp = $request->input('timestamp');
        $status->currentTime = $request->input('current_time');
        $status->paused = $request->input('paused');

        if (config('ycsplayer.debug')) {
            $status->log('player seek', [
                'roomId' => $roomId,
                'mode' => 'seek',
                'user' => auth()->user()->name,
            ]);
        }

        $this->statusCache->store($roomId, $status);

        PlayerSeeked::broadcast($socketId, $roomId, $status)->toOthers();

        return response()->noContent();
    }

    public function timeUpdate(Request $request)
    {
        $request->validate([
            'room_id' => ['required', 'string', 'max:12'],
            'timestamp' => ['required', 'numeric'],
            'current_time' => ['required', 'numeric'],
            'paused' => ['required', 'boolean'],
        ]);

        $roomId = $this->getRoomId($request);

        if (! $this->statusCache->get($roomId)) {
            return response()->noContent();
        }

        $status = $this->statusCache->get($roomId);
        $status->timestamp = $request->input('timestamp');
        $status->currentTime = $request->input('current_time');
        $status->paused = $request->input('paused');

        if (config('ycsplayer.debug')) {
            $status->log('player time update', [
                'roomId' => $roomId,
                'mode' => 'time update',
                'user' => auth()->user()->name,
            ]);
        }

        $this->statusCache->store($roomId, $status);

        return response()->noContent();
    }

    public function end(Request $request)
    {
        $request->validate([
            'room_id' => ['required', 'string', 'max:12'],
        ]);

        $roomId = $this->getRoomId($request);

        if (config('ycsplayer.debug')) {
            $status = $this->statusCache->get($roomId);
            $status->log('player end', [
                'roomId' => $roomId,
                'mode' => 'end',
                'user' => auth()->user()->name,
            ]);
        }

        $this->statusCache->delete($roomId);

        return response()->noContent();
    }

    public function debug(Request $request)
    {
        $request->validate([
            'room_id' => ['required', 'string', 'max:12'],
        ]);

        $roomId = $this->getRoomId($request);

        $status = $this->statusCache->get($roomId);

        return response()->json([
            'status' => [
                'timestamp' => $status?->timestamp
                    ? (int) floor($status->timestamp / 1000)
                    : null,
                'datetime' => $status?->timestamp
                    ? date('Y-m-d H:i:s', (int) floor($status->timestamp / 1000))
                    : null,
                'current_time' => $status?->currentTime ?? null,
                'is_clicked_big_button' => $status?->isClickedBigButton ?? null,
                'paused' => $status?->paused ?? null,
            ],
            'logs' => $status?->logs ?? [],
        ]);
    }

    protected function getRoomId(Request $request): string
    {
        /** @var string */
        $roomId = $request->input('room_id');

        if ($this->playerGuard->check($roomId)) {
            return $roomId;
        }

        if (! $intRoomId = Room::decodeHashId($roomId)) {
            abort(404);
        }

        $room = Room::findOrFail($intRoomId, ['id']);

        $this->authorize('view', $room);

        $this->playerGuard->authorized($roomId);

        return $roomId;
    }
}
