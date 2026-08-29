<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlayerGuessedCorrectly implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public string $roomCode;
    public string $playerName;
    public int $points;

    public function __construct(string $roomCode, string $playerName, int $points)
    {
        $this->roomCode = $roomCode;
        $this->playerName = $playerName;
        $this->points = $points;
    }

    public function broadcastOn(): array
    {
        return [new Channel('room.' . $this->roomCode)];
    }
}