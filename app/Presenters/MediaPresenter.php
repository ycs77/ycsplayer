<?php

namespace App\Presenters;

use AdditionApps\FlexiblePresenter\FlexiblePresenter;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaPresenter extends FlexiblePresenter
{
    public function values(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name.'.'.$this->extension,
            'thumbnail' => $this->hasGeneratedConversion('thumb')
                ? $this->getUrl('thumb')
                : null,
        ];
    }

    public function presetPlay()
    {
        return $this->with(fn (Media $media) => [
            'src' => $media->getUrl(),
            'preview' => $this->hasGeneratedConversion('preview')
                ? $this->getUrl('preview')
                : null,
        ]);
    }
}
