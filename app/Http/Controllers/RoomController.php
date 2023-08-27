<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Presenters\MediaPresenter;
use App\Presenters\PlaylistItemPresenter;
use App\Presenters\RoomMemberPresenter;
use App\Presenters\RoomPresenter;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class RoomController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User */
        $user = Auth::user();

        /** @var \Illuminate\Pagination\LengthAwarePaginator */
        $rooms = $user->rooms()->paginate(20);

        return Inertia::render('Room/Index', [
            'rooms' => RoomPresenter::collection($rooms->withQueryString()),
        ]);
    }

    public function show(Room $room)
    {
        $this->authorize('view', $room);

        /** @var \App\Models\User */
        $user = Auth::user();

        return Inertia::render('Room/Show', [
            'room' => fn () => RoomPresenter::make($room)->preset('show'),
            'current_playing' => fn () => PlaylistItemPresenter::make($room->current_playing)->preset('play'),
            'playlist_items' => fn () => PlaylistItemPresenter::collection($room->playlist_items),
            'medias' => fn () => $user->can('operatePlaylistItem', $room)
                ? MediaPresenter::collection($room->getMedia())
                : [],
            'members' => fn () => RoomMemberPresenter::collection($room->membersForPresent()),
            'can' => fn () => [
                'operatePlaylistItem' => $user->can('operatePlaylistItem', $room),
                'inviteMember' => $user->can('inviteMember', $room),
                'removeMember' => $user->can('removeMember', $room),
                'uploadMedias' => $user->can('uploadMedias', $room),
                'settings' => $user->can('settings', $room),
            ],
        ]);
    }

    public function members(Room $room)
    {
        $this->authorize('view', $room);

        /** @var \App\Models\User */
        $user = Auth::user();

        return Inertia::render('Room/Members', [
            'room' => fn () => RoomPresenter::make($room),
            'members' => fn () => RoomMemberPresenter::collection($room->membersForPresent()),
            'can' => fn () => [
                'inviteMember' => $user->can('inviteMember', $room),
                'removeMember' => $user->can('removeMember', $room),
                'uploadMedias' => $user->can('uploadMedias', $room),
                'settings' => $user->can('settings', $room),
            ],
        ]);
    }
}
