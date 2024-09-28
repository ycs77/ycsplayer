<?php

namespace App\Models\Concerns;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

trait DeletesUnusedAttachment
{
    /**
     * Delete the unused attachment.
     */
    protected static function deleteUnusedAttachment(string $attribute): void
    {
        static::deleteUnusedAttachmentFromDisk($attribute, null);
    }

    /**
     * Delete the unused attachment from the given disk.
     *
     * @param  string|\Illuminate\Contracts\Filesystem\Filesystem|null  $disk
     */
    protected static function deleteUnusedAttachmentFromDisk(string $attribute, $disk): void
    {
        static::saved(function (self $model) use ($attribute, $disk) {
            if (! $disk instanceof Filesystem) {
                /** @var \Illuminate\Contracts\Filesystem\Filesystem $disk */
                $disk = Storage::disk($disk);
            }

            if ($model->isDirty($attribute) &&
                $model->getOriginal($attribute) &&
                $disk->exists($model->getOriginal($attribute))
            ) {
                $disk->delete($model->getOriginal($attribute));
            }
        });

        static::deleted(function (self $model) use ($attribute, $disk) {
            if (! $disk instanceof Filesystem) {
                /** @var \Illuminate\Contracts\Filesystem\Filesystem $disk */
                $disk = Storage::disk($disk);
            }

            if ($model->getAttribute($attribute) &&
                $disk->exists($model->getAttribute($attribute))
            ) {
                $disk->delete($model->getAttribute($attribute));
            }
        });
    }
}
