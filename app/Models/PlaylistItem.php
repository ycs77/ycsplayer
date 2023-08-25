<?php

namespace App\Models;

use App\Enums\PlayerType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property \App\Enums\PlayerType $type
 * @property string $title
 * @property string $url
 * @property string|null $thumbnail
 * @property string|null $preview
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
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
        'preview',
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
