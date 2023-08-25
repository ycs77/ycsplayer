<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Presenters\MediaPresenter;
use App\Presenters\RoomPresenter;
use Inertia\Inertia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class RoomMediaController extends Controller
{
    public function index(Room $room)
    {
        return Inertia::render('Room/Medias', [
            'room' => fn () => RoomPresenter::make($room)->preset('show'),
            'csrf_token' => fn () => csrf_token(),
            'medias' => fn () => MediaPresenter::collection($room->getMedia()),
        ]);
    }

    public function delete(Room $room, Media $media)
    {
        $media->delete();
    }
}
