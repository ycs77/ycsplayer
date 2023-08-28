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

    public function get(string $roomId, bool $log = true): ?PlayStatus
    {
        $status = $this->cache->get($this->key($roomId));

        if (config('player.log_enabled') && $log) {
            logger('play status cache get: '.$roomId, [
                'status' => $status?->toArray(),
                'user_id' => auth()->id(),
            ]);
        }

        return $status;
    }

    public function store(string $roomId, PlayStatus $status): void
    {
        $this->cache->put(
            $this->key($roomId), $status, $this->ttl()
        );

        if (config('player.log_enabled')) {
            logger('play status cache set: '.$roomId, [
                'status' => $status->toArray(),
                'user_id' => auth()->id(),
            ]);
        }
    }

    public function delete(string $roomId): void
    {
        $this->cache->delete($this->key($roomId));

        if (config('player.log_enabled')) {
            logger('play status cache delete: '.$roomId, [
                'user_id' => auth()->id(),
            ]);
        }
    }

    protected function key(string $roomId): string
    {
        return 'play-status:'.$roomId;
    }

    /**
     * @return \DateTimeInterface|\DateInterval|int|null
     */
    protected function ttl()
    {
        return now()->addHours(3);
    }
}
