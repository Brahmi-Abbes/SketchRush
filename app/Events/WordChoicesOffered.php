<?php

namespace App\Events;

use App\Models\GamePlayer;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WordChoicesOffered implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public array $choices;
    protected int $playerId;

    public function __construct(GamePlayer $drawer, array $choices)
    {
        $this->playerId = $drawer->id;
        $this->choices = $choices;
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('player.' . $this->playerId)];
    }
}