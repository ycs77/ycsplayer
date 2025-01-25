<?php

namespace App\MediaLibrary;

use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAdded;
use Spatie\MediaLibrary\MediaCollections\Exceptions\DiskCannotBeAccessed;
use Spatie\MediaLibrary\MediaCollections\Filesystem as MediaLibraryFilesystem;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * 修改函數目的是為了防止再次從遠端的硬碟下載檔案回來。
 * 改成直接讀取本地的檔案生成完縮圖之後才上傳。
 */
class Filesystem extends MediaLibraryFilesystem
{
    public function add(string $file, Media $media, ?string $targetFileName = null): bool
    {
        app(FileManipulator::class)->createDerivedFilesFromLocal($media, $file);

        try {
            $this->copyToMediaLibrary($file, $media, null, $targetFileName);
        } catch (DiskCannotBeAccessed $exception) {
            return false;
        }

        event(new MediaHasBeenAdded($media));

        return true;
    }
}
