<?php

namespace App\Jobs;

use App\Facades\CDN;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class RemoveRoomMediaFile
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Media $media
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $purgePaths = collect()
            ->push($this->media->getPath())
            ->concat(
                collect($this->media->generated_conversions)
                    ->filter(fn (bool $has) => $has)
                    ->map(fn (bool $has, string $name) => $this->media->getPath($name))
            )
            ->toArray();

        $this->media->delete();

        $cdn = CDN::disk(config('media-library.disk_name'));

        dispatch(function () use ($cdn, $purgePaths) {
            foreach ($purgePaths as $path) {
                $cdn->purge($path);
            }
        });
    }
}
