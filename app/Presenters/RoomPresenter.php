<?php

namespace App\Presenters;

use AdditionApps\FlexiblePresenter\FlexiblePresenter;
use App\Models\Room;

class RoomPresenter extends FlexiblePresenter
{
    public function values(): array
    {
        return [
            'id' => $this->hash_id,
            'type' => $this->type->value,
            'title' => $this->title,
            'auto_play' => $this->auto_play,
            'auto_remove' => $this->auto_remove,
        ];
    }

    public function presetShow()
    {
        return $this->with(fn (Room $room) => [
            'note' => $room->note,
        ]);
    }
}
