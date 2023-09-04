<?php

namespace App\Services\Cdn;

use App\Contracts\CdnService;
use Illuminate\Support\Facades\Http;

class DOCdnService implements CdnService
{
    public function purge(string $fileName)
    {
        Http::asJson()->delete(config('filesystems.disks.do.cdn_endpoint').'/cache', [
            'files' => [$fileName],
        ]);
    }
}
