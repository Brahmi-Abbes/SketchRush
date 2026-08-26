<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Services\GameStateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class GameController extends Controller
{
    public function show(Request $request, string $code, GameStateService $stateService)
    {
        $game = Game::where('room_code', $code)->with('players')->firstOrFail();

        if (! Redis::exists("game:{$game->room_code}:turn_order")) {
            $stateService->startGame($game);
        }

        $player = auth('players')->user();
        $isDrawer = (string) Redis::get("game:{$game->room_code}:current_drawer_id") === (string) $player->id;

        $pendingChoices = null;
        if ($isDrawer && Redis::exists("game:{$game->room_code}:pending_choices")) {
            $pendingChoices = json_decode(Redis::get("game:{$game->room_code}:pending_choices"), true);
        }

        return view('game', [
            'game' => $game,
            'isDrawer' => $isDrawer,
            'pendingChoices' => $pendingChoices,
        ]);
    }

    public function selectWord(Request $request, string $code, GameStateService $stateService)
    {
        $game = Game::where('room_code', $code)->firstOrFail();
        $stateService->selectWord($game, auth('players')->user(), $request->input('word'));

        return response()->noContent();
    }
}