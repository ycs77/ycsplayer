<?php

namespace App\Http\Controllers;

use App\Events\PlayerlistItemClicked;
use App\Models\PlaylistItem;
use App\Models\Room;
use App\Presenters\PlaylistItemPresenter;
use App\Presenters\RoomPresenter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class RoomController extends Controller
{
    public function index()
    {
        $rooms = RoomPresenter::collection(
            Room::paginate(20)->withQueryString()
        );

        return Inertia::render('Room/Index', [
            'rooms' => $rooms,
        ]);
    }

    public function show(Room $room)
    {
        $room->load('playlist_items');

        if (count($room->playlist_items) && ! $room->current_playing_id) {
            $room->update([
                'current_playing_id' => $room->playlist_items->first()->id,
            ]);
        }

        return Inertia::render('Room/Show', [
            'room' => fn () => RoomPresenter::make($room)->preset('show'),
            'current_playing' => fn () => PlaylistItemPresenter::make($room->current_playing),
            'playlist_items' => fn () => PlaylistItemPresenter::collection($room->playlist_items),
        ]);
    }

    public function clickMedia(Room $room, PlaylistItem $item)
    {
        $this->playItem($room, $item);

        PlayerlistItemClicked::broadcast($room->id)->toOthers();

        return back();
    }

    public function nextMedia(Request $request, Room $room)
    {
        if (! $room->current_playing_id) {
            return back();
        }

        if ($room->current_playing_id !== $request->input('current_playing_id')) {
            return back();
        }

        $canUpdate = false;
        $removedItemId = null;

        DB::transaction(function () use ($room, &$canUpdate, &$removedItemId) {
            $currentPlaying = $room->current_playing()
                ->lockForUpdate()
                ->first();

            if ($currentPlaying) {
                $removedItemId = $currentPlaying->id;
                $currentPlaying->delete();
                $canUpdate = true;
            }
        });

        if (! $canUpdate) {
            return back();
        }

        if (! count($room->playlist_items)) {
            $room->update([
                'current_playing_id' => null,
            ]);

            return back();
        }

        /** @var \App\Models\PlaylistItem */
        $item = $room->playlist_items->first();

        $this->playItem($room, $item);

        return back();
    }

    protected function playItem(Room $room, PlaylistItem $item)
    {
        $room->update([
            'current_playing_id' => $item->id,
        ]);

        Cache::delete('room:'.$room->id);
    }
}
