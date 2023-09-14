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
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * @property int $id
 * @property \App\Enums\RoomType $type
 * @property string $name
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
        'name',
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
            "rooms.{$this->id}.change-member-role",
            "rooms.{$this->id}.remove-member",
            "rooms.{$this->id}.upload-medias",
            "rooms.{$this->id}.settings",
            "rooms.{$this->id}.delete",
        ];
    }

    public function roomRoles(): array
    {
        return [
            "rooms.{$this->id}.user" => [
                "rooms.{$this->id}.operate-playlist-item",
            ],
            "rooms.{$this->id}.uploader" => [
                "rooms.{$this->id}.operate-playlist-item",
                "rooms.{$this->id}.upload-medias",
            ],
            "rooms.{$this->id}.admin" => [
                "rooms.{$this->id}.operate-playlist-item",
                "rooms.{$this->id}.invite-member",
                "rooms.{$this->id}.change-member-role",
                "rooms.{$this->id}.remove-member",
                "rooms.{$this->id}.upload-medias",
                "rooms.{$this->id}.settings",
                "rooms.{$this->id}.delete",
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
            ->prepend(tap($user, fn (User $user) => $user->online = true))
            ->map(function (User $member) {
                /** @var string */
                $role = $member->getRoleNames()
                    ->first(fn (string $role) => Str::startsWith($role, "rooms.{$this->id}."));

                if ($role) {
                    $member->role_name = Str::after($role, "rooms.{$this->id}.");
                }

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
        $user->getRoleNames()
            ->filter(fn (string $role) => str_starts_with($role, 'rooms.'.$this->id.'.'))
            ->each(fn (string $role) => $user->removeRole($role));

        $this->members()->detach($user);
    }

    public function changeMemberRole(User $user, string $role): void
    {
        $user->getRoleNames()
            ->filter(fn (string $role) => str_starts_with($role, 'rooms.'.$this->id.'.'))
            ->each(fn (string $role) => $user->removeRole($role));

        $user->assignRole('rooms.'.$this->id.'.'.$role);
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
                    'created_at' => now(),
                    'updated_at' => now(),
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
        if (config('app.env') === 'testing') {
            return;
        }

        /** @phpstan-ignore-next-line */
        $this->addMediaConversion('thumb')
            ->width(120)
            ->height(68)
            ->extractVideoFrameAtSecond(30)
            ->nonQueued();

        /** @phpstan-ignore-next-line */
        $this->addMediaConversion('preview')
            ->width(1280)
            ->height(720)
            ->extractVideoFrameAtSecond(30)
            ->nonQueued();
    }
}
