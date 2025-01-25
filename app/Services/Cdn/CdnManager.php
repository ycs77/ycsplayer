<?php

namespace App\Services\Cdn;

use App\Contracts\CdnService;
use Illuminate\Support\Manager;

class CdnManager extends Manager
{
    protected $supportedDisks = ['null', 'do'];

    public function getDefaultDriver()
    {
        return 'null';
    }

    public function disk(?string $disk = null): CdnService
    {
        return $this->driver(in_array($disk, $this->supportedDisks) ? $disk : null);
    }

    protected function createNullDriver()
    {
        return new NullCdnService;
    }

    protected function createDoDriver()
    {
        return new DOCdnService;
    }
}
