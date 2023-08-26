<?php

namespace App\Models;

use App\Enums\RoomType;
use App\Models\Concerns\HasHashId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
 * @property \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $members
 */
class Room extends Model implements HasMedia
{
    use HasFactory;
    use HasHashId;
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

    public function current_playing(): BelongsTo
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

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'room_member', 'room_id', 'member_id');
    }

    public function join(User $user, string $role = 'user'): void
    {
        $this->members()->attach($user);

        $user->assignRole('rooms.'.$this->id.'.'.$role);
    }

    public function leave(User $user): void
    {
        $user->removeRole($user->getRoleNames());

        $this->members()->detach($user);
    }

    public function isMember(User $user): bool
    {
        return $this->members()
            ->where('id', $user->id)
            ->exists();
    }

    public function doesntMember(User $user): bool
    {
        return ! $this->isMember($user);
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
