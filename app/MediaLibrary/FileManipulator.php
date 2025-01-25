<?php

namespace App\MediaLibrary;

use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\Conversions\Actions\PerformConversionAction;
use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\Conversions\ConversionCollection;
use Spatie\MediaLibrary\Conversions\FileManipulator as MediaLibraryFileManipulator;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * 修改函數目的是為了防止再次從遠端的硬碟下載檔案回來。
 * 改成直接讀取本地的檔案生成完縮圖之後才上傳。
 */
class FileManipulator extends MediaLibraryFileManipulator
{
    public function createDerivedFilesFromLocal(
        Media $media,
        string $file,
        array $onlyConversionNames = [],
        bool $onlyMissing = false,
    ): void {
        if (! $this->canConvertMedia($media)) {
            return;
        }

        $conversions = ConversionCollection::createForMedia($media)
            ->filter(function (Conversion $conversion) use ($onlyConversionNames) {
                if (count($onlyConversionNames) === 0) {
                    return true;
                }

                return in_array($conversion->getName(), $onlyConversionNames);
            })
            ->filter(fn (Conversion $conversion) => $conversion->shouldBePerformedOn($media->collection_name))
            ->reject(fn (Conversion $conversion) => $conversion->shouldBeQueued());

        $this->performConversionsFromLocal($conversions, $media, $file, $onlyMissing);
    }

    public function performConversionsFromLocal(
        ConversionCollection $conversions,
        Media $media,
        string $file,
        bool $onlyMissing = false
    ): self {
        if ($conversions->isEmpty()) {
            return $this;
        }

        $conversions
            ->reject(function (Conversion $conversion) use ($onlyMissing, $media) {
                $relativePath = $media->getPath($conversion->getName());

                if ($rootPath = config("filesystems.disks.{$media->disk}.root")) {
                    $relativePath = str_replace($rootPath, '', $relativePath);
                }

                return $onlyMissing && Storage::disk($media->disk)->exists($relativePath);
            })
            ->each(function (Conversion $conversion) use ($media, $file) {
                (new PerformConversionAction)->execute($conversion, $media, $file);

                if (is_file($conversionFile = pathinfo($file, PATHINFO_DIRNAME).'/'.$conversion->getConversionFile($media))) {
                    unlink($conversionFile);
                }
            });

        if (is_file($conversionFile = pathinfo($file, PATHINFO_DIRNAME).'/'.pathinfo($file, PATHINFO_FILENAME).'.jpg')) {
            unlink($conversionFile);
        }

        return $this;
    }
}
