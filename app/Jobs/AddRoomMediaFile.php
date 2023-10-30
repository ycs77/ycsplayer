<?php

namespace App\Jobs;

use App\Events\RoomMediaConverted;
use App\Models\QueueRoomFile;
use App\Models\Room;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class AddRoomMediaFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Room $room,
        public QueueRoomFile $queueFile,
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        /** @var \Illuminate\Filesystem\FilesystemAdapter */
        $disk = Storage::disk($this->queueFile->disk);
        $path = $disk->path($this->queueFile->path);

        $fileAddr = $this->room->addMedia($path);
        $fileAddr->usingName($this->queueFile->name);
        $fileAddr->usingFileName(basename($this->queueFile->path));
        $fileAddr->preservingOriginal();

        /** @var \Spatie\MediaLibrary\MediaCollections\Models\Media */
        $media = $fileAddr->toMediaCollection();

        unset($fileAddr);

        gc_collect_cycles();

        unlink($path);

        $this->queueFile->delete();

        $media->update(['converting' => false]);

        $message = config('queue.default') === 'sync'
            ? '媒體檔案上傳成功'
            : '媒體檔案處理完成';

        RoomMediaConverted::broadcast($this->room->hash_id, $message);
    }
}
