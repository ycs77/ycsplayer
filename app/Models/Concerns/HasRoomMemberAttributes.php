<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * @property bool $online
 * @property string|null $role_name
 */
trait HasRoomMemberAttributes
{
    protected function online(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => $attributes['online'] ?? false,
            set: fn (bool $online) => $online,
        );
    }

    protected function roleName(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => $attributes['role_name'] ?? null,
            set: fn (?string $roleName) => $roleName,
        );
    }
}
