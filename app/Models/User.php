<?php

namespace App\Models;

use App\Models\Concerns\HasHashId;
use App\Models\Concerns\HasRoomMemberAttributes;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon $email_verified_at
 * @property string $password
 * @property string|null $avatar
 * @property-read string|null $avatar_url
 * @property string $remember_token
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Database\Eloquent\Collection<int, \App\Models\Room> $rooms
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens;
    use HasFactory;
    use HasHashId;
    use HasRoles;
    use HasRoomMemberAttributes;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    protected static function booted(): void
    {
        static::deleting(function (User $user) {
            if ($user->avatar && Storage::exists($user->avatar)) {
                Storage::delete($user->avatar);
            }
        });
    }

    protected function avatarUrl(): Attribute
    {
        return Attribute::make(function () {
            if ($this->avatar) {
                if (filter_var($this->avatar, FILTER_VALIDATE_URL)) {
                    return $this->avatar;
                }

                return Storage::url($this->avatar);
            }
        });
    }

    public function rooms(): BelongsToMany
    {
        return $this->belongsToMany(Room::class, 'room_member', 'member_id', 'room_id');
    }
}
