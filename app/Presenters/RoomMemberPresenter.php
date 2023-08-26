<?php

namespace App\Presenters;

use AdditionApps\FlexiblePresenter\FlexiblePresenter;

class RoomMemberPresenter extends FlexiblePresenter
{
    public function values(): array
    {
        return [
            'id' => $this->hash_id,
            'name' => $this->name,
            'email' => $this->email,
            'avatar' => $this->avatar_url,
            'online' => $this->online,
        ];
    }
}
