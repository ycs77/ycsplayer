<?php

namespace App\Http\Controllers;

use App\Events\PlayerlistItemClicked;
use App\Models\PlaylistItem;
use App\Models\Room;
use App\Player\PlayStatusCacheRepository;
use App\Presenters\PlaylistItemPresenter;
use App\Presenters\RoomPresenter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class RoomController extends Controller
{
    public function __construct(
        protected PlayStatusCacheRepository $statusCache,
    ) {
        //
    }

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
            'current_playing' => fn () => PlaylistItemPresenter::make($room->current_playing),
            'playlist_items' => fn () => PlaylistItemPresenter::collection($room->playlist_items),
        ]);
    }

    public function members(Room $room)
    {
        return Inertia::render('Room/Members', [
            'room' => fn () => RoomPresenter::make($room)->preset('show'),
        ]);
    }

    public function files(Room $room)
    {
        return Inertia::render('Room/Files', [
            'room' => fn () => RoomPresenter::make($room)->preset('show'),
        ]);
    }

    public function settings(Room $room)
    {
        return Inertia::render('Room/Settings', [
            'room' => fn () => RoomPresenter::make($room)->preset('show'),
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

        $requestedCurrentPlayingId = $request->input('current_playing_id');

        if ($room->current_playing_id !== $requestedCurrentPlayingId) {
            return back();
        }

        $roomId = $room->id;

        /** @var \App\Models\PlaylistItem|null */
        $item = null;

        DB::transaction(function () use ($roomId, $requestedCurrentPlayingId, &$item) {
            /** @var \App\Models\Room */
            $room = Room::query()
                ->lockForUpdate()
                ->find($roomId);

            if ($room->current_playing_id === $requestedCurrentPlayingId) {
                // 提前先計算當前播放項目的 index
                $itemIndex = $room->playlist_items
                    ->search(fn ($item) => $item->id === $room->current_playing_id);

                // 播放完畢後自動刪除
                if ($room->auto_remove) {
                    $room->current_playing->delete();
                }

                // 播放完畢後自動順位播下一個
                // 注意這裡的 `$room->playlist_items` 還是舊的快取，包含未刪除的當前播放項目。
                if (is_numeric($itemIndex)) {
                    $item = $room->playlist_items[$itemIndex + 1] ?? null;
                }

                $this->playItem($room, $item);
            }
        });

        return back();
    }

    protected function playItem(Room $room, ?PlaylistItem $item)
    {
        $room->current_playing_id = $item?->id;
        $room->save();

        $this->statusCache->delete($room->id);
    }
}
