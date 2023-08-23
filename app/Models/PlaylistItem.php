<?php

namespace App\Models;

use App\Enums\PlayerType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property \App\Enums\PlayerType $type
 * @property string $title
 * @property string $url
 * @property string|null $thumbnail
 * @property \App\Models\Room $room
 */
class PlaylistItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'title',
        'url',
        'thumbnail',
        'room_id',
    ];

    protected $casts = [
        'type' => PlayerType::class,
        'room_id' => 'integer',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}
