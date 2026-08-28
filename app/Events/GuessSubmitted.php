<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GuessSubmitted implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public string $roomCode;
    public string $playerName;
    public string $guess;

    public function __construct(string $roomCode, string $playerName, string $guess)
    {
        $this->roomCode = $roomCode;
        $this->playerName = $playerName;
        $this->guess = $guess;
    }

    public function broadcastOn(): array
    {
        return [new Channel('room.' . $this->roomCode)];
    }
}