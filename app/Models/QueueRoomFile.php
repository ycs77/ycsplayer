<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property string $name
 * @property string $path
 * @property string $disk
 * @property \Illuminate\Support\Carbon $expired_at
 */
class QueueRoomFile extends Model
{
    protected $fillable = [
        'name',
        'path',
        'disk',
        'expired_at',
        'room_id',
    ];

    protected $casts = [
        'expired_at' => 'datetime',
        'room_id' => 'integer',
    ];

    protected static function booted(): void
    {
        static::deleted(function (QueueRoomFile $queueFile) {
            if (Storage::disk($queueFile->disk)->exists($queueFile->path)) {
                Storage::disk($queueFile->disk)->delete($queueFile->path);
            }
        });
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function expired()
    {
        return $this->expired_at->isPast();
    }

    public function scopeExpired(Builder $query)
    {
        $query->where('expired_at', '<', now());
    }

    public function loadingMedia(): Media
    {
        $mediaClass = config('media-library.media_model');
        /** @var \Spatie\MediaLibrary\MediaCollections\Models\Media $loadingMedia */
        $loadingMedia = new $mediaClass;

        $loadingMedia->name = $this->name;
        $loadingMedia->file_name = basename($this->path);
        $loadingMedia->converting = true;

        return $loadingMedia;
    }
}
