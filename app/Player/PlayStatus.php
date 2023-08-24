<?php

namespace App\Player;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

class PlayStatus implements JsonSerializable, Arrayable
{
    /** 現在的 timestamp 時間 */
    public int $timestamp = 0;

    /** 當前影片播放秒數 */
    public ?float $currentTime = null;

    /** 影片已點擊過大按鈕 */
    public bool $isClickedBigButton = true;

    /** 當前影片暫停狀態 */
    public bool $paused = true;

    public function __construct(array $data = [])
    {
        $this->timestamp = $data['timestamp'] ?? $this->timestamp;
        $this->currentTime = $data['current_time'] ?? $this->currentTime;
        $this->isClickedBigButton = $data['is_clicked_big_button'] ?? $this->isClickedBigButton;
        $this->paused = $data['paused'] ?? $this->paused;
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function toArray(): array
    {
        return [
            'timestamp' => $this->timestamp,
            'current_time' => $this->currentTime,
            'is_clicked_big_button' => $this->isClickedBigButton,
            'paused' => $this->paused,
        ];
    }
}
