<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Presenters\MediaPresenter;
use App\Presenters\PlaylistItemPresenter;
use App\Presenters\RoomPresenter;
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
        return Inertia::render('Room/Show', [
            'room' => fn () => RoomPresenter::make($room)->preset('show'),
            'current_playing' => fn () => PlaylistItemPresenter::make($room->current_playing)->preset('play'),
            'playlist_items' => fn () => PlaylistItemPresenter::collection($room->playlist_items),
            'medias' => fn () => MediaPresenter::collection($room->getMedia()),
        ]);
    }

    public function members(Room $room)
    {
        return Inertia::render('Room/Members', [
            'room' => fn () => RoomPresenter::make($room),
        ]);
    }
}
