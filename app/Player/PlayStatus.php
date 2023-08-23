<?php

namespace App\Player;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

class PlayStatus implements JsonSerializable, Arrayable
{
    /** 現在的 timestamp 時間 */
    public int $timestamp = 0;

    /** 當前影片播放秒數 */
    public float $currentTime = 0.0;

    /** 影片狀態為已開始 */
    public bool $isStarted = true;

    /** 當前影片暫停狀態 */
    public bool $paused = true;

    public function __construct(array $data = [])
    {
        $this->timestamp = $data['timestamp'] ?? $this->timestamp;
        $this->currentTime = $data['current_time'] ?? $this->currentTime;
        $this->isStarted = $data['is_started'] ?? $this->isStarted;
        $this->paused = $data['paused'] ?? $this->paused;
    }

    public function jsonSerialize(): array {
        return $this->toArray();
    }

    public function toArray(): array
    {
        return [
            'timestamp' => $this->timestamp,
            'current_time' => $this->currentTime,
            'is_started' => $this->isStarted,
            'paused' => $this->paused,
        ];
    }
}
