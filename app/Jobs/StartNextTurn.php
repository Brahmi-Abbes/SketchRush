<?php

namespace App\Jobs;

use App\Models\Game;
use App\Services\GameStateService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class StartNextTurn implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $roomCode) {}

    public function handle(GameStateService $gameState): void
    {
        $game = Game::where('room_code', $this->roomCode)->firstOrFail();
        if ($game->status === 'playing') {
            $gameState->startTurn($game);
        }
    }
}