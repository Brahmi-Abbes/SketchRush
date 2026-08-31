<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Services\GameStateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use App\Events\StrokeDrawn;
use App\Events\CanvasCleared;
use App\Events\GuessSubmitted;
use App\Events\PlayerGuessedCorrectly;
use App\Events\ClueRevealed;

class GameController extends Controller
{
    public function show(Request $request, string $code, GameStateService $stateService)
    {
        $game = Game::where('room_code', $code)->with('players')->firstOrFail();

        $player = auth('players')->user();
        if (! $player) {
            return redirect()->route('home');
        }

        if (! Redis::exists("game:{$game->room_code}:turn_order")) {
            $stateService->startGame($game);
        }

        $isDrawer = (string) Redis::get("game:{$game->room_code}:current_drawer_id") === (string) $player->id;

        $pendingChoices = null;
        if ($isDrawer && Redis::exists("game:{$game->room_code}:pending_choices")) {
            $pendingChoices = json_decode(Redis::get("game:{$game->room_code}:pending_choices"), true);
        }

        return view('game', [
            'game' => $game,
            'isDrawer' => $isDrawer,
            'pendingChoices' => $pendingChoices,
            'cluesRemaining' => 3 - $player->clues_used,
        ]);
    }

    public function selectWord(Request $request, string $code, GameStateService $stateService)
    {
        $game = Game::where('room_code', $code)->firstOrFail();
        $stateService->selectWord($game, auth('players')->user(), $request->input('word'));

        return response()->noContent();
    }

    public function draw(Request $request, string $code)
    {
        $game = Game::where('room_code', $code)->firstOrFail();
        $player = auth('players')->user();

        
        abort_unless($player, 403);

        $drawerId = Redis::get("game:{$game->room_code}:current_drawer_id");
        abort_unless((string) $drawerId === (string) $player->id, 403);

        event(new StrokeDrawn(
            $game->room_code,
            $request->input('points'),
            $request->input('color', '#000000'),
            (int) $request->input('width', 3),
            (int) $request->input('generation', 0)
        ));

        return response()->noContent();
    }

    public function clearCanvas(Request $request, string $code)
    {
        $game = Game::where('room_code', $code)->firstOrFail();
        $player = auth('players')->user();

        
        abort_unless($player, 403);

        $drawerId = Redis::get("game:{$game->room_code}:current_drawer_id");
        abort_unless((string) $drawerId === (string) $player->id, 403);

        event(new CanvasCleared($game->room_code));

        return response()->noContent();
    }
    
    public function guess(Request $request, string $code)
    {
        $game = Game::where('room_code', $code)->firstOrFail();
        $player = auth('players')->user();
        abort_unless($player, 403);

        $drawerId = Redis::get("game:{$game->room_code}:current_drawer_id");
        abort_if((string) $drawerId === (string) $player->id, 403, 'The drawer cannot guess');

        $word = Redis::get("game:{$game->room_code}:current_word");
        abort_unless($word, 409, 'No word is being drawn right now');

        $guess = trim((string) $request->input('guess', ''));
        abort_if($guess === '', 422);

        if (Redis::sismember("game:{$game->room_code}:correct_guessers", $player->id)) {
            return response()->noContent(); // already solved it this round, ignore
        }

        if (strcasecmp($guess, $word) === 0) {
            Redis::sadd("game:{$game->room_code}:correct_guessers", $player->id);

            $points = app(GameStateService::class)->calculatePoints($game->room_code, $player->id);            app(GameStateService::class)->addScore($game->room_code, $player->id, $points);

            event(new PlayerGuessedCorrectly($game->room_code, $player->guest_name, $points));

            $totalGuessers = $game->players()->count() - 1;
            $correctCount = Redis::scard("game:{$game->room_code}:correct_guessers");
            if ($totalGuessers > 0 && $correctCount >= $totalGuessers) {
                app(GameStateService::class)->endRound(
                    $game->room_code,
                    (int) Redis::get("game:{$game->room_code}:round_token"),
                    'all_guessed'
                );
            }
        } else {
            event(new GuessSubmitted($game->room_code, $player->guest_name, $guess));
        }

        return response()->noContent();
    }

    public function requestClue(Request $request, string $code)
    {
        $game = Game::where('room_code', $code)->firstOrFail();
        $player = auth('players')->user();
        abort_unless($player, 403);

        $drawerId = Redis::get("game:{$game->room_code}:current_drawer_id");
        abort_if((string) $drawerId === (string) $player->id, 403, 'The drawer cannot use clues');

        abort_if($player->clues_used >= 3, 422, 'No clues remaining');

        if (Redis::sismember("game:{$game->room_code}:correct_guessers", $player->id)) {
            abort(422, 'You already guessed correctly');
        }

        $word = Redis::get("game:{$game->room_code}:current_word");
        abort_unless($word, 409, 'No word is being drawn right now');

        $player->increment('clues_used');
        Redis::sadd("game:{$game->room_code}:clue_used_this_round:" . $player->id, $game->room_code);

        $wordLength = mb_strlen($word);
        $maxRevealable = max(1, $wordLength - 1); // never reveal the very last letter
        $revealedLetters = min($player->clues_used, $maxRevealable);
        $hint = strtoupper(mb_substr($word, 0, $revealedLetters)) . str_repeat('_', $wordLength - $revealedLetters);

        event(new ClueRevealed($player->id, $hint, 3 - $player->clues_used));
        
        return response()->noContent();
    }
}