<?php

namespace App\Room;

use Illuminate\Contracts\Cache\Repository as CacheRepository;

class RoomNoteEditorRepository
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

    public function store(string $roomId, array $user): void
    {
        $this->cache->put($this->key($roomId), $user, $this->ttl());
    }

    public function start(string $roomId, string $userId, string $userName): void
    {
        $this->store($roomId, [
            'id' => $userId,
            'name' => $userName,
        ]);
    }

    public function end(string $roomId): void
    {
        $this->cache->delete($this->key($roomId));
    }

    public function isEditing(string $roomId): bool
    {
        return $this->get($roomId) !== null;
    }

    public function resetWhenCurrentEditingUserRefreshPage(string $roomId, string $userId, $callback = null)
    {
        $user = $this->get($roomId);

        if ($userId === ($user['id'] ?? null)) {
            $this->end($roomId);

            if ($callback) {
                $callback();
            }
        }
    }

    protected function key(string $roomId): string
    {
        return 'room-note-editor:'.$roomId;
    }

    /**
     * @return \DateTimeInterface|\DateInterval|int|null
     */
    protected function ttl()
    {
        return now()->addHour();
    }
}
