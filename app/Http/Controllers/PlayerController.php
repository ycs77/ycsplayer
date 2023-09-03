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
use Vinkla\Hashids\Facades\Hashids;

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

        if (config('ycsplayer.log_enabled')) {
            logger('player play: '.$roomId, [
                'user_id' => auth()->id(),
            ]);
        }

        $socketId = $request->header('X-Socket-Id');

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
            'room_id' => ['required', 'string', 'max:12'],
            'current_time' => ['required', 'numeric'],
        ]);

        $roomId = $this->getRoomId($request);

        if (config('ycsplayer.log_enabled')) {
            logger('player pause: '.$roomId, [
                'user_id' => auth()->id(),
            ]);
        }

        $socketId = $request->header('X-Socket-Id');

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
            'room_id' => ['required', 'string', 'max:12'],
            'timestamp' => ['required', 'numeric'],
            'current_time' => ['required', 'numeric'],
            'paused' => ['required', 'boolean'],
        ]);

        $roomId = $this->getRoomId($request);

        if (config('ycsplayer.log_enabled')) {
            logger('player seek: '.$roomId, [
                'user_id' => auth()->id(),
            ]);
        }

        $socketId = $request->header('X-Socket-Id');

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
            'room_id' => ['required', 'string', 'max:12'],
            'timestamp' => ['required', 'numeric'],
            'current_time' => ['required', 'numeric'],
            'paused' => ['required', 'boolean'],
        ]);

        $roomId = $this->getRoomId($request);

        if (! $this->statusCache->get($roomId, false)) {
            return response()->noContent();
        }

        if (config('ycsplayer.log_enabled')) {
            logger('player time update: '.$roomId, [
                'user_id' => auth()->id(),
            ]);
        }

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
            'room_id' => ['required', 'string', 'max:12'],
        ]);

        $roomId = $this->getRoomId($request);

        if (config('ycsplayer.log_enabled')) {
            logger('player end: '.$roomId, [
                'user_id' => auth()->id(),
            ]);
        }

        $this->statusCache->delete($roomId);

        return response()->noContent();
    }

    protected function getRoomId(Request $request): string
    {
        /** @var string */
        $roomId = $request->input('room_id');

        if ($this->playerGuard->check($roomId)) {
            return $roomId;
        }

        if (! $intRoomId = current(Hashids::connection('rooms')->decode($roomId))) {
            abort(404);
        }

        $room = Room::findOrFail($intRoomId, ['id']);

        $this->authorize('view', $room);

        $this->playerGuard->authorized($roomId);

        return $roomId;
    }
}
