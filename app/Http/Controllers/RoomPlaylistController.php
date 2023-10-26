<?php

namespace App\Http\Controllers;

use App\Enums\PlayerType;
use App\Events\PlayerlistItemAdded;
use App\Events\PlayerlistItemClicked;
use App\Events\PlayerlistItemNexted;
use App\Events\PlayerlistItemRemoved;
use App\Models\PlaylistItem;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class RoomPlaylistController extends Controller
{
    public function store(Request $request, Room $room)
    {
        $this->authorize('operatePlaylistItem', $room);

        $request->validate([
            'type' => [new Enum(PlayerType::class)],
            'title' => ['required', 'string', 'max:50'],
            'url' => [
                'required_if:type,youtube',
                'nullable',
                'string',
                'max:100',
            ],
            'media_id' => [
                'required_if:type,video,audio',
                'nullable',
                Rule::exists(Media::class, 'uuid'),
            ],
        ]);

        $type = PlayerType::from($request->input('type'));

        if ($type === PlayerType::Video || $type === PlayerType::Audio) {
            /** @var \Spatie\MediaLibrary\MediaCollections\Models\Media|null */
            $media = $room->media()
                ->where('uuid', $request->input('media_id'))
                ->first();

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
            $url = $request->input('url');

            foreach ([
                '/^https?:\/\/(?:www|music)\.youtube\.com\/watch\?v\=([\w-]+)(?:&.+)?$/',
                '/^https?:\/\/(?:www|music)\.youtube\.com\/watch\?.+&v\=([\w-]+)(?:&.+)?$/',
                '/^https?:\/\/www\.youtube\.com\/embed\/([\w-]+)(?:\?.+)?$/',
                '/^https?:\/\/youtu\.be\/([\w-]+)(?:\?.+)?$/',
            ] as $pattern) {
                if (preg_match($pattern, $url, $m)) {
                    $youtubeId = $m[1];
                    break;
                }
            }

            if (! isset($youtubeId)) {
                throw ValidationException::withMessages([
                    'url' => 'YouTube 的網址請輸入 https://www.youtube.com/watch?v=<id> 或 https://www.youtube.com/embed/<id> 格式',
                ]);
            }

            $url = "https://www.youtube.com/watch?v={$youtubeId}";

            $room->playlist_items()->create([
                'type' => $type,
                'title' => $request->input('title'),
                'url' => $url,
                'thumbnail' => "https://img.youtube.com/vi/{$youtubeId}/default.jpg",
            ]);
        }

        PlayerlistItemAdded::broadcast($room->hash_id)->toOthers();
    }

    public function youtubeTitle(Request $request)
    {
        $url = $request->input('url');

        if (Str::startsWith($url, [
            'https://www.youtube.com/watch?v=',
            'https://music.youtube.com/watch?v=',
            'https://youtu.be/',
        ])) {
            $oembed = Http::get('https://youtube.com/oembed?type=json&url='.rawurlencode($url))->json();
            $title = $oembed['title'] ?? '';

            return response($title);
        }
    }

    public function click(Room $room, PlaylistItem $item)
    {
        $this->authorize('view', $room);

        $this->updateCurrentPlayingItem($room, $item);

        PlayerlistItemClicked::broadcast($room->hash_id)->toOthers();
    }

    public function destroy(Room $room, PlaylistItem $item)
    {
        $this->authorize('operatePlaylistItem', $room);

        if ($item->id === $room->current_playing_id) {
            $this->changeToNextPlaylistItem(
                room: $room,
                requestedCurrentPlayingId: $room->current_playing_id,
                autoRemove: true,
                callback: fn () => PlayerlistItemNexted::broadcast($room->hash_id)->toOthers(),
            );
        } else {
            $item->delete();

            PlayerlistItemRemoved::broadcast($room->hash_id)->toOthers();
        }
    }

    public function next(Request $request, Room $room)
    {
        $this->authorize('view', $room);

        $request->validate([
            'current_playing_id' => ['required', 'string', 'max:20'],
        ]);

        if ($room->current_playing_id) {
            // 解碼 Hash ID
            $requestedCurrentPlayingId = PlaylistItem::decodeHashId(
                $request->input('current_playing_id')
            );

            if ($room->current_playing_id === $requestedCurrentPlayingId) {
                $this->changeToNextPlaylistItem(
                    room: $room,
                    requestedCurrentPlayingId: $requestedCurrentPlayingId,
                    autoRemove: $room->auto_remove,
                    callback: fn () => PlayerlistItemNexted::broadcast($room->hash_id)->toOthers(),
                );
            }
        }
    }

    protected function changeToNextPlaylistItem(Room $room,
        int $requestedCurrentPlayingId,
        bool $autoRemove,
        callable $callback = null)
    {
        $roomId = $room->id;

        /** @var \App\Models\PlaylistItem|null */
        $item = null;

        DB::transaction(function () use (
            $roomId, &$item, $requestedCurrentPlayingId, $autoRemove, $callback
        ) {
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

                $this->updateCurrentPlayingItem($room, $item);

                if (is_callable($callback)) {
                    $callback();
                }
            }
        });
    }

    protected function updateCurrentPlayingItem(Room $room, ?PlaylistItem $item)
    {
        $room->update(['current_playing_id' => $item?->id]);
    }
}
