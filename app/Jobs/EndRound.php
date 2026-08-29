<?php

namespace App\Jobs;

use App\Services\GameStateService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class EndRound implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $roomCode,
        public int $token,
    ) {}

    public function handle(GameStateService $gameState): void
    {
        $gameState->endRound($this->roomCode, $this->token, 'timeout');
    }
}
