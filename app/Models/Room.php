<?php

namespace App\Models;

use App\Enums\RoomType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property int $id
 * @property \App\Enums\RoomType $type
 * @property string $title
 * @property int|null $current_playing_id
 * @property bool $auto_play
 * @property bool $auto_remove
 * @property string|null $note
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \App\Models\PlaylistItem|null $current_playing
 * @property \Illuminate\Database\Eloquent\Collection<int, \App\Models\PlaylistItem> $playlist_items
 */
class Room extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $fillable = [
        'type',
        'title',
        'current_playing_id',
        'auto_play',
        'auto_remove',
        'note',
    ];

    protected $casts = [
        'type' => RoomType::class,
        'current_playing_id' => 'integer',
        'auto_play' => 'boolean',
        'auto_remove' => 'boolean',
    ];

    public function current_playing()
    {
        return $this->belongsTo(PlaylistItem::class);
    }

    public function playlist_items(): HasMany
    {
        return $this->hasMany(PlaylistItem::class);
    }

    public function onPlayerPlayed(PlaylistItem $currentItem): void
    {
        if ($this->auto_remove) {
            $currentItem->delete();
        }
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(120)
            ->height(68)
            ->extractVideoFrameAtSecond(30)
            ->nonQueued();

        $this->addMediaConversion('preview')
            ->width(1280)
            ->height(720)
            ->extractVideoFrameAtSecond(30)
            ->nonQueued();
    }
}
