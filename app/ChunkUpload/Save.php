<?php

namespace App\ChunkUpload;

use Illuminate\Support\Facades\Cache;
use Pion\Laravel\ChunkUpload\Save\ParallelSave;

class Save extends ParallelSave
{
    protected function buildFullFileFromChunks()
    {
        parent::buildFullFileFromChunks();

        /** @var \App\ChunkUpload\UploadHandler */
        $handler = $this->handler();

        // 清除臨時檔名快取
        Cache::delete($handler->getFileNameCacheKey());
    }
}
