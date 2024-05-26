<?php

namespace App\Services\Cdn;

use App\Contracts\CdnService;

class NullCdnService implements CdnService
{
    public function purge(string|array $files): void
    {
        //
    }
}
