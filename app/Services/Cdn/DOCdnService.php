<?php

namespace App\Services\Cdn;

use App\Contracts\CdnService;
use Illuminate\Support\Facades\Http;

class DOCdnService implements CdnService
{
    public function purge(string|array $files): void
    {
        Http::asJson()
            ->withToken(config('services.digitalocean.api_token'))
            ->delete(config('filesystems.disks.do.cdn_endpoint').'/cache', [
                'files' => is_array($files) ? $files : [$files],
            ]);
    }
}
