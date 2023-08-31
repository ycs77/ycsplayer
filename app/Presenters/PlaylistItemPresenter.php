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
            'title' => $this->title,
            'thumbnail' => $this->thumbnail,
        ];
    }

    public function presetPlay()
    {
        return $this->with(fn () => [
            'type' => $this->type->value,
            'url' => $this->url,
            'preview' => $this->preview,
        ]);
    }
}
