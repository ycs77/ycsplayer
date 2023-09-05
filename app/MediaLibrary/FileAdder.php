<?php

namespace App\MediaLibrary;

use Closure;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\Conversions\ImageGenerators\Image as ImageGenerator;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Exceptions\DiskCannotBeAccessed;
use Spatie\MediaLibrary\MediaCollections\FileAdder as MediaLibraryFileAdder;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\ResponsiveImages\Jobs\GenerateResponsiveImagesJob;
use Spatie\MediaLibrary\Support\RemoteFile;

class FileAdder extends MediaLibraryFileAdder
{
    /** @var \Closure|null */
    protected $onModelCreatedCallback;

    public function onModelCreated(Closure $callback)
    {
        $this->onModelCreatedCallback = $callback;

        return $this;
    }

    protected function processMediaItem(HasMedia $model, Media $media, MediaLibraryFileAdder $fileAdder)
    {
        $this->guardAgainstDisallowedFileAdditions($media);

        $this->checkGenerateResponsiveImages($media);

        if (! $media->getConnectionName()) {
            /** @var \Illuminate\Database\Eloquent\Model $model */
            $media->setConnection($model->getConnectionName());
        }

        $model->media()->save($media);

        // 觸發 Media model created 事件
        if (! is_null($this->onModelCreatedCallback)) {
            ($this->onModelCreatedCallback)($media);
        }

        /** @phpstan-ignore-next-line */
        if ($fileAdder->file instanceof RemoteFile) {
            $addedMediaSuccessfully = $this->filesystem->addRemote($fileAdder->file, $media, $fileAdder->fileName);
        } else {
            $addedMediaSuccessfully = $this->filesystem->add($fileAdder->pathToFile, $media, $fileAdder->fileName);
        }

        if (! $addedMediaSuccessfully) {
            $media->forceDelete();

            throw DiskCannotBeAccessed::create($media->disk);
        }

        if (! $fileAdder->preserveOriginal) {
            /** @phpstan-ignore-next-line */
            if ($fileAdder->file instanceof RemoteFile) {
                Storage::disk($fileAdder->file->getDisk())->delete($fileAdder->file->getKey());
            } else {
                unlink($fileAdder->pathToFile);
            }
        }

        if ($this->generateResponsiveImages && (new ImageGenerator())->canConvert($media)) {
            $generateResponsiveImagesJobClass = config('media-library.jobs.generate_responsive_images', GenerateResponsiveImagesJob::class);

            $job = new $generateResponsiveImagesJobClass($media);

            if ($customConnection = config('media-library.queue_connection_name')) {
                $job->onConnection($customConnection);
            }

            if ($customQueue = config('media-library.queue_name')) {
                $job->onQueue($customQueue);
            }

            dispatch($job);
        }

        if ($collectionSizeLimit = optional($this->getMediaCollection($media->collection_name))->collectionSizeLimit) {
            /** @phpstan-ignore-next-line */
            $collectionMedia = $this->subject->fresh()->getMedia($media->collection_name);

            if ($collectionMedia->count() > $collectionSizeLimit) {
                $model->clearMediaCollectionExcept($media->collection_name, $collectionMedia->slice(-$collectionSizeLimit, $collectionSizeLimit));
            }
        }
    }
}
