<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoundEnded implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public string $roomCode;
    public string $word;
    public string $reason;

    public function __construct(string $roomCode, string $word, string $reason)
    {
        $this->roomCode = $roomCode;
        $this->word = $word;
        $this->reason = $reason;
    }

    public function broadcastOn(): array
    {
        return [new Channel('room.' . $this->roomCode)];
    }
}