<?php

namespace App\Observers;

use App\Events\PlayerlistItemClicked;
use App\Events\PlayerlistItemRemoved;
use App\Models\PlaylistItem;
use App\Player\PlayStatusCacheRepository;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaObserver
{
    /**
     * Handle the Media "deleted" event.
     */
    public function deleted(Media $media): void
    {
        /** @var \App\Models\PlaylistItem|null */
        $item = PlaylistItem::where('url', $media->getUrl())->first();

        if ($item) {
            $itemId = $item->id;
            $room = $item->room;

            $item->delete();

            if ($itemId === $room->current_playing_id) {
                $room->update(['current_playing_id' => null]);

                app(PlayStatusCacheRepository::class)->delete($room->hash_id);

                PlayerlistItemClicked::broadcast($room->hash_id)->toOthers();
            } else {
                PlayerlistItemRemoved::broadcast($room->hash_id)->toOthers();
            }
        }
    }
}
