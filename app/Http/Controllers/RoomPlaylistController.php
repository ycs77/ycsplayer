<?php

namespace App\Http\Controllers;

use App\Enums\PlayerType;
use App\Events\PlayerlistItemAdded;
use App\Events\PlayerlistItemClicked;
use App\Events\PlayerlistItemRemoved;
use App\Models\PlaylistItem;
use App\Models\Room;
use App\Player\PlayStatusCacheRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class RoomPlaylistController extends Controller
{
    public function __construct(
        protected PlayStatusCacheRepository $statusCache,
    ) {
        //
    }

    public function store(Request $request, Room $room)
    {
        $request->validate([
            'type' => [new Enum(PlayerType::class)],
            'title' => ['required', 'string', 'max:20'],
            'url' => [
                'required_if:type,youtube',
                'nullable',
                'string',
                'max:100',
            ],
            'media_id' => [
                'required_if:type,video,audio',
                'nullable',
                Rule::exists(Media::class, 'id'),
            ],
        ]);

        $type = PlayerType::from($request->input('type'));

        if ($type === PlayerType::Video || $type === PlayerType::Audio) {
            /** @var \Spatie\MediaLibrary\MediaCollections\Models\Media */
            $media = $room->media()->find($request->input('media_id'));

            if (! $media) {
                throw ValidationException::withMessages([
                    'media_id' => '無法加入此媒體',
                ]);
            }

            $room->playlist_items()->create([
                'type' => $type,
                'title' => $request->input('title'),
                'url' => $media->getUrl(),
                'thumbnail' => $media->hasGeneratedConversion('thumb')
                    ? $media->getUrl('thumb')
                    : null,
                'preview' => $media->hasGeneratedConversion('preview')
                    ? $media->getUrl('preview')
                    : null,
            ]);
        } elseif ($type === PlayerType::YouTube) {
            if (preg_match('/^https?:\/\/www\.youtube\.com\/watch\?v\=([\w-]+)$/', $request->input('url'), $m)) {
                $youtubeId = $m[1];
            } elseif (preg_match('/^https?:\/\/www\.youtube\.com\/embed\/([\w-]+)$/', $request->input('url'), $m)) {
                $youtubeId = $m[1];
            }

            if (! isset($youtubeId)) {
                throw ValidationException::withMessages([
                    'url' => 'YouTube 的網址請輸入 https://www.youtube.com/watch?v=<id> 或 https://www.youtube.com/embed/<id> 格式',
                ]);
            }

            $room->playlist_items()->create([
                'type' => $type,
                'title' => $request->input('title'),
                'url' => $request->input('url'),
                'thumbnail' => "https://img.youtube.com/vi/{$youtubeId}/default.jpg",
            ]);
        }

        PlayerlistItemAdded::broadcast($room->id)->toOthers();
    }

    public function click(Room $room, PlaylistItem $item)
    {
        $this->playItem($room, $item);

        PlayerlistItemClicked::broadcast($room->id)->toOthers();
    }

    public function destroy(Room $room, PlaylistItem $item)
    {
        if ($item->id === $room->current_playing_id) {
            $this->changeToNextPlaylistItem(
                $room, $room->current_playing_id, true
            );

            PlayerlistItemClicked::broadcast($room->id)->toOthers();
        } else {
            $item->delete();

            PlayerlistItemRemoved::broadcast($room->id)->toOthers();
        }
    }

    public function next(Request $request, Room $room)
    {
        if ($room->current_playing_id) {
            $requestedCurrentPlayingId = $request->input('current_playing_id');

            if ($room->current_playing_id === $requestedCurrentPlayingId) {
                $this->changeToNextPlaylistItem(
                    $room, $requestedCurrentPlayingId, $room->auto_remove
                );
            }
        }
    }

    protected function changeToNextPlaylistItem(Room $room, int $requestedCurrentPlayingId, bool $autoRemove)
    {
        $roomId = $room->id;

        /** @var \App\Models\PlaylistItem|null */
        $item = null;

        DB::transaction(function () use ($roomId, &$item, $requestedCurrentPlayingId, $autoRemove) {
            /** @var \App\Models\Room */
            $room = Room::query()
                ->lockForUpdate()
                ->find($roomId);

            if ($room->current_playing_id === $requestedCurrentPlayingId) {
                // 提前先計算當前播放項目的 index
                $itemIndex = $room->playlist_items
                    ->search(fn ($item) => $item->id === $room->current_playing_id);

                // 播放完畢後自動刪除
                if ($autoRemove) {
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
    }

    protected function playItem(Room $room, ?PlaylistItem $item)
    {
        $room->current_playing_id = $item?->id;
        $room->save();

        $this->statusCache->delete($room->id);
    }
}
