<?php

namespace App\Presenters;

use AdditionApps\FlexiblePresenter\FlexiblePresenter;

/**
 * @mixin \App\Models\PlaylistItem
 */
class PlaylistItemPresenter extends FlexiblePresenter
{
    public function values(): array
    {
        return [
            'id' => $this->hash_id,
            'type' => $this->type->value,
            'title' => $this->title,
            'thumbnail' => $this->thumbnail,
        ];
    }

    public function presetPlay()
    {
        return $this->with(fn () => [
            'url' => $this->url,
            'preview' => $this->preview,
        ]);
    }
}
