<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StrokeDrawn implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public string $roomCode;
    public array $points;
    public string $color;
    public int $width;
    public int $generation;

    public function __construct(string $roomCode, array $points, string $color, int $width, int $generation)
    {
        $this->roomCode = $roomCode;
        $this->points = $points;
        $this->color = $color;
        $this->width = $width;
        $this->generation = $generation;
    }

    public function broadcastOn(): array
    {
        return [new Channel('room.' . $this->roomCode)];
    }
}