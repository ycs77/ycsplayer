<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \App\Contracts\CdnService disk(string|null $disk = null)
 * @method static void purge(string $fileName)
 */
class CDN extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return \App\Services\Cdn\CdnManager::class;
    }
}
