<?php

namespace App\Presenters;

use AdditionApps\FlexiblePresenter\FlexiblePresenter;
use App\Models\PlaylistItem;

class PlaylistItemPresenter extends FlexiblePresenter
{
    public function values(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'thumbnail' => $this->thumbnail,
        ];
    }

    public function presetPlay()
    {
        return $this->with(fn (PlaylistItem $item) => [
            'type' => $item->type->value,
            'url' => $item->url,
        ]);
    }
}
