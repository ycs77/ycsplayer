<?php

namespace App\Contracts;

interface CdnService
{
    public function purge(string|array $files): void;
}
