<?php

namespace App\ChunkUpload;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Pion\Laravel\ChunkUpload\Handler\ResumableJSUploadHandler;

class UploadHandler extends ResumableJSUploadHandler
{
    protected ?string $fileNameCacheKey = null;

    public function startSaving($chunkStorage)
    {
        return new Save(
            $this->file, $this, $chunkStorage, $this->config
        );
    }

    public function createChunkFileName($additionalName = null, $currentChunkIndex = null)
    {
        if ($additionalName !== null) {
            // 將 resumableIdentifier 的值雜湊過並截前6碼
            $additionalName = substr(md5($additionalName), 0, 6);
        }

        $filename = parent::createChunkFileName($additionalName, $currentChunkIndex);

        // 把當前的檔名換成臨時檔名，並存入快取，避免在檔名上出現問題
        if (! $tempName = Cache::get($this->getFileNameCacheKey())) {
            Cache::put(
                $this->getFileNameCacheKey(), $tempName = Str::random(6), now()->addDay()
            );
        }

        $filename = str_replace(
            $this->file->getClientOriginalName(),
            $tempName.'.'.$this->file->getClientOriginalExtension(),
            $filename
        );

        return $filename;
    }

    public function getFileNameCacheKey(): string
    {
        if (! $this->fileNameCacheKey) {
            $array = [];

            // ensure that the chunk name is for unique for the client session
            $useSession = $this->config->chunkUseSessionForName();
            $useBrowser = $this->config->chunkUseBrowserInfoForName();
            if ($useSession && false === static::canUseSession()) {
                $useBrowser = true;
                $useSession = false;
            }

            // the session needs more config on the provider
            if ($useSession) {
                $array[] = Session::getId();
            }

            // can work without any additional setup
            if ($useBrowser) {
                $array[] = md5($this->request->ip().$this->request->header('User-Agent', 'no-browser'));
            }

            $key = implode('-', $array);

            $this->fileNameCacheKey = 'chunk-upload-file-name:'.$key;
        }

        return $this->fileNameCacheKey;
    }
}
