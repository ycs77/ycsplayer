<?php

namespace App\Room;

use Illuminate\Contracts\Cache\Repository as CacheRepository;

class RoomOnlineMembersCacheRepository
{
    public function __construct(
        protected CacheRepository $cache,
    ) {
        //
    }

    public function get(string $roomId): ?array
    {
        return $this->cache->get($this->key($roomId));
    }

    public function store(string $roomId, array $members): void
    {
        $this->cache->put(
            $this->key($roomId), $members, $this->ttl()
        );
    }

    public function online(string $roomId, string $userId): void
    {
        $members = $this->get($roomId) ?? [];
        $members[] = $userId;

        $this->store($roomId, $members);
    }

    public function offline(string $roomId, string $userId): void
    {
        $members = $this->get($roomId) ?? [];
        $members = array_filter($members, fn (string $id) => $id !== $userId);

        $this->store($roomId, $members);
    }

    public function has(string $roomId, string $userId): bool
    {
        return in_array($userId, $this->get($roomId) ?? []);
    }

    public function clear(string $roomId): void
    {
        $this->cache->delete($this->key($roomId));
    }

    protected function key(string $roomId): string
    {
        return 'room-members:'.$roomId;
    }

    /**
     * @return \DateTimeInterface|\DateInterval|int|null
     */
    protected function ttl()
    {
        return now()->addHours(3);
    }
}
