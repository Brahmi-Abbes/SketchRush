<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GameStarted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $roomCode;

    public function __construct(string $roomCode)
    {
        $this->roomCode = $roomCode;
    }

    public function broadcastOn(): array
    {
        return [new Channel('room.' . $this->roomCode)];
    }
}