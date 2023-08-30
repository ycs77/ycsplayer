<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Presenters\MediaPresenter;
use App\Presenters\RoomPresenter;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class RoomMediaController extends Controller
{
    public function index(Room $room)
    {
        $this->authorize('uploadMedias', $room);

        /** @var \App\Models\User */
        $user = Auth::user();

        return Inertia::render('Room/Medias', [
            'room' => fn () => RoomPresenter::make($room),
            'csrf_token' => fn () => csrf_token(),
            'medias' => fn () => MediaPresenter::collection($room->getMedia()),
            'can' => fn () => [
                'uploadMedias' => $user->can('uploadMedias', $room),
                'settings' => $user->can('settings', $room),
            ],
        ]);
    }

    public function destroy(Room $room, Media $media)
    {
        $this->authorize('uploadMedias', $room);

        $media->delete();
    }
}
