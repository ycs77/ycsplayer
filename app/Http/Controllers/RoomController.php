<?php

namespace App\Http\Controllers;

use App\Enums\RoomType;
use App\Facades\Flash;
use App\Models\Room;
use App\Presenters\MediaPresenter;
use App\Presenters\PlaylistItemPresenter;
use App\Presenters\RoomMemberPresenter;
use App\Presenters\RoomPresenter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;
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
            'can' => fn () => [
                'create' => $user->can('create', Room::class),
            ],
        ])->title('房間列表');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Room::class);

        /** @var \App\Models\User */
        $user = Auth::user();

        $request->validate([
            'name' => ['required', 'string', 'max:20'],
            'type' => [new Enum(RoomType::class)],
            'auto_play' => ['required', 'boolean'],
            'auto_remove' => ['required', 'boolean'],
        ]);

        /** @var \App\Models\Room */
        $room = Room::create($request->only('name', 'type', 'auto_play', 'auto_remove'));

        $room->join($user, 'admin');

        Flash::success('房間創建成功');

        return redirect()->route('rooms.show', $room);
    }

    public function show(Room $room)
    {
        $this->authorize('view', $room);

        /** @var \App\Models\User */
        $user = Auth::user();

        return Inertia::render('Room/Show', [
            'room' => fn () => RoomPresenter::make($room)->preset('show'),
            'currentPlaying' => fn () => PlaylistItemPresenter::make($room->current_playing)->preset('play'),
            'playlistItems' => fn () => PlaylistItemPresenter::collection($room->playlist_items),
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
        ])->title($room->name);
    }

    public function note(Request $request, Room $room)
    {
        $this->authorize('view', $room);

        $request->validate([
            'note' => ['nullable', 'string', 'max:500'],
        ], [], [
            'note' => '記事本',
        ]);

        $room->update($request->only('note'));

        Flash::success('記事本更新成功');
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
        ])->title($room->name);
    }

    public function edit(Room $room)
    {
        $this->authorize('settings', $room);

        /** @var \App\Models\User */
        $user = Auth::user();

        return Inertia::render('Room/Settings', [
            'room' => fn () => RoomPresenter::make($room),
            'can' => fn () => [
                'uploadMedias' => $user->can('uploadMedias', $room),
                'delete' => $user->can('delete', $room),
            ],
        ])->title($room->name);
    }

    public function update(Request $request, Room $room)
    {
        $this->authorize('settings', $room);

        $request->validate([
            'type' => [new Enum(RoomType::class)],
            'auto_play' => ['required', 'boolean'],
            'auto_remove' => ['required', 'boolean'],
        ]);

        $room->update($request->only('type', 'auto_play', 'auto_remove'));

        Flash::success('房間設定更新成功');
    }

    public function destroy(Room $room)
    {
        $this->authorize('delete', $room);

        $room->delete();

        Flash::success('房間刪除成功');

        return redirect()->route('rooms.index');
    }
}
