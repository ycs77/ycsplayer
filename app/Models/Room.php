<?php

namespace App\Models;

use App\Enums\RoomType;
use App\Models\Concerns\HasHashId;
use App\Room\RoomOnlineMembersRepository;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use PHPUnit\Framework\Attributes\After;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

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

    public function roomPermissions(): array
    {
        return [
            "rooms.{$this->id}.operate-playlist-item",
            "rooms.{$this->id}.invite-member",
            "rooms.{$this->id}.remove-member",
            "rooms.{$this->id}.upload-medias",
            "rooms.{$this->id}.settings",
        ];
    }

    public function roomRoles(): array
    {
        return [
            "rooms.{$this->id}.user" => [
                "rooms.{$this->id}.operate-playlist-item",
            ],
            "rooms.{$this->id}.admin" => [
                "rooms.{$this->id}.operate-playlist-item",
                "rooms.{$this->id}.invite-member",
                "rooms.{$this->id}.remove-member",
                "rooms.{$this->id}.upload-medias",
                "rooms.{$this->id}.settings",
            ],
        ];
    }

    protected static function booted(): void
    {
        static::created(function (Room $room) {
            app(PermissionRegistrar::class)->forgetCachedPermissions();

            $room->attachRoomPermissions();
        });

        static::deleting(function (Room $room) {
            $room->detachRoomPermissions();

            app(PermissionRegistrar::class)->forgetCachedPermissions();
        });
    }

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

    public function membersForPresent()
    {
        $user = Auth::user();

        return $this->members
            ->filter(fn (User $member) => $member->id !== $user->id)
            ->map(function (User $member) {
                $member->online = app(RoomOnlineMembersRepository::class)->has($this->hash_id, $member->hash_id);

                return $member;
            })
            ->prepend(tap($user, fn (user $user) => $user->online = true))
            ->map(function (User $member) {
                /** @var array */
                $roleNames = $member->getRoleNames();
                $member->role_name = preg_replace('/rooms\.\d+\./', '', $roleNames[0]);

                return $member;
            });
    }

    public function join(User $user, string $role = 'user'): void
    {
        $this->members()->attach($user);

        $user->assignRole('rooms.'.$this->id.'.'.$role);
    }

    public function leave(User $user): void
    {
        $user->getRoleNames()->each(fn (string $role) => $user->removeRole($role));

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

    public function attachRoomPermissions(): void
    {
        Permission::insert(
            collect($this->roomPermissions())
                ->map(fn (string $permissionName) => [
                    'name' => $permissionName,
                    'guard_name' => 'web',
                ])
                ->toArray()
        );

        foreach ($this->roomRoles() as $roleName => $permissionsName) {
            Role::create(['name' => $roleName])
                ->givePermissionTo($permissionsName);
        }
    }

    public function detachRoomPermissions(): void
    {
        Role::where('name', 'like', 'rooms.'.$this->id.'.%')->delete();

        Permission::where('name', 'like', 'rooms.'.$this->id.'.%')->delete();
    }

    public function syncRoomPermissions(): void
    {
        $usersRoles = $this->members->map(fn (User $member) => [
            'member' => $member,
            'roles' => $member->getRoleNames(),
        ]);

        $this->detachRoomPermissions();

        $this->attachRoomPermissions();

        $usersRoles->each(function (array $item) {
            $item['roles']->each(fn (string $role) => $item['member']->assignRole($role));
        });
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
