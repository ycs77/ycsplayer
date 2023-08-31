<?php

namespace App\Presenters;

use AdditionApps\FlexiblePresenter\FlexiblePresenter;

/**
 * @mixin \App\Models\User
 *
 * @property bool $online
 * @property string $role_name
 */
class RoomMemberPresenter extends FlexiblePresenter
{
    public function values(): array
    {
        return [
            'id' => $this->hash_id,
            'name' => $this->name,
            'email' => $this->email,
            'avatar' => $this->avatar_url,
            'role' => $this->role_name, // @see \App\Models\Room@membersForPresent()
            'online' => $this->online, // @see \App\Models\Room@membersForPresent()
        ];
    }
}
