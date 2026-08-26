<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoundStarted implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public string $roomCode;
    public string $drawerName;

    public function __construct(string $roomCode, string $drawerName)
    {
        $this->roomCode = $roomCode;
        $this->drawerName = $drawerName;
    }

    public function broadcastOn(): array
    {
        return [new Channel('room.' . $this->roomCode)];
    }
}