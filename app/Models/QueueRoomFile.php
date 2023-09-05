<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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
    ];

    protected $casts = [
        'expired_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::deleted(function (QueueRoomFile $queueFile) {
            if (Storage::disk($queueFile->disk)->exists($queueFile->path)) {
                Storage::disk($queueFile->disk)->delete($queueFile->path);
            }
        });
    }

    public function expired()
    {
        return $this->expired_at->isPast();
    }

    public function scopeExpired(Builder $query)
    {
        $query->where('expired_at', '<', now());
    }
}
