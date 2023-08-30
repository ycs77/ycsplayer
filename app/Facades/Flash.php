<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void success(string $message)
 * @method static void error(string $message)
 */
class Flash extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return \App\Inertia\Flash::class;
    }
}
