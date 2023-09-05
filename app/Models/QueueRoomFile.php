<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * @property string $name
 * @property string $path
 * @property string $disk
 */
class QueueRoomFile extends Model
{
    protected $fillable = [
        'name',
        'path',
        'disk',
    ];

    protected static function booted(): void
    {
        static::deleted(function (QueueRoomFile $queueFile) {
            if (Storage::disk($queueFile->disk)->exists($queueFile->path)) {
                Storage::disk($queueFile->disk)->delete($queueFile->path);
            }
        });
    }
}
