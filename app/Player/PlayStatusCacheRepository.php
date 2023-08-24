<?php

namespace App\Player;

use Illuminate\Contracts\Cache\Repository as CacheRepository;

class PlayStatusCacheRepository
{
    public function __construct(
        protected CacheRepository $cache,
    ) {
        //
    }

    public function get(string $roomId): ?PlayStatus
    {
        return $this->cache->get($this->key($roomId));
    }

    public function store(string $roomId, PlayStatus $status): void
    {
        $this->cache->put(
            $this->key($roomId), $status, $this->ttl()
        );
    }

    public function delete(string $roomId): void
    {
        $this->cache->delete($this->key($roomId));
    }

    protected function key(string $roomId): string
    {
        return 'room:'.$roomId;
    }

    /**
     * @return \DateTimeInterface|\DateInterval|int|null
     */
    protected function ttl()
    {
        return now()->addHours(12);
    }
}
