<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ClueRevealed implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    protected int $playerId;
    public string $hint;
    public int $cluesRemaining;

    public function __construct(int $playerId, string $hint, int $cluesRemaining)
    {
        $this->playerId = $playerId;
        $this->hint = $hint;
        $this->cluesRemaining = $cluesRemaining;
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('player.' . $this->playerId)];
    }
}