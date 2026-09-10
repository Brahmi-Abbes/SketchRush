<?php

namespace App\Services;

use App\Events\RoundStarted;
use App\Events\RoundEnded;
use App\Events\TurnAwaitingWord;
use App\Events\LetterAutoRevealed;
use App\Jobs\EndRound;
use App\Jobs\AutoRevealLetter;
use App\Models\Game;
use App\Models\GamePlayer;
use Illuminate\Support\Facades\Redis;

class GameStateService
{
    private const ROUND_SECONDS = 80;
    public const MAX_CLUES = 3;

    public function startGame(Game $game): void
    {
        $playerIds = $game->players()->pluck('id')->all();
        shuffle($playerIds);

        Redis::set("game:{$game->room_code}:turn_order", json_encode($playerIds));
        Redis::set("game:{$game->room_code}:turn_index", 0);
        Redis::set("game:{$game->room_code}:round", 1);

        $this->startTurn($game);
    }

    public function startTurn(Game $game): void
    {
        $order = json_decode(Redis::get("game:{$game->room_code}:turn_order"), true);
        $index = (int) Redis::get("game:{$game->room_code}:turn_index");
        $drawerId = $order[$index];
        $drawer = GamePlayer::find($drawerId);

        $categories = array_rand(config('words'), 3);
        $choices = [];
        foreach ($categories as $category) {
            $words = config("words.{$category}");
            $choices[] = [
                'word' => $words[array_rand($words)],
                'category' => $category,
            ];
        }

        Redis::set("game:{$game->room_code}:pending_choices", json_encode($choices));
        Redis::set("game:{$game->room_code}:current_drawer_id", $drawerId);
        foreach ($game->players as $p) {
            Redis::del("game:{$game->room_code}:clue_reveal_count:{$p->id}");
        }
        Redis::del("game:{$game->room_code}:current_word");
        Redis::incr("game:{$game->room_code}:round_token");
        Redis::del("game:{$game->room_code}:correct_guessers");

        event(new TurnAwaitingWord($game->room_code, $drawer->guest_name));
    }

    public function selectWord(Game $game, GamePlayer $player, string $word): void
    {
        $drawerId = Redis::get("game:{$game->room_code}:current_drawer_id");
        abort_unless((string) $drawerId === (string) $player->id, 403);

        $choices = json_decode(Redis::get("game:{$game->room_code}:pending_choices"), true);
        $valid = collect($choices)->firstWhere('word', $word);
        abort_unless($valid, 422, 'Invalid word choice');

        Redis::set("game:{$game->room_code}:current_word", $word);
        $endsAt = now()->addSeconds(self::ROUND_SECONDS)->timestamp;
        Redis::set("game:{$game->room_code}:round_ends_at", $endsAt);
        $token = (int) Redis::get("game:{$game->room_code}:round_token");

        EndRound::dispatch($game->room_code, $token)->delay(now()->addSeconds(self::ROUND_SECONDS));

        Redis::set("game:{$game->room_code}:auto_reveal_count", 0);

        AutoRevealLetter::dispatch($game->room_code, $token)
            ->delay(now()->addSeconds((int) (self::ROUND_SECONDS * 0.4)));

        AutoRevealLetter::dispatch($game->room_code, $token)
            ->delay(now()->addSeconds((int) (self::ROUND_SECONDS * 0.7)));
        Redis::del("game:{$game->room_code}:pending_choices");

        event(new RoundStarted($game->room_code, $player->guest_name, $endsAt));
    }

    public function endRound(string $roomCode, int $token, string $reason): void
    {
        $currentToken = (int) Redis::get("game:{$roomCode}:round_token");
        if ($token !== $currentToken) {
            return;
        }

        $word = Redis::get("game:{$roomCode}:current_word");
        if (!$word) {
            return;
        }

        $game = Game::where('room_code', $roomCode)->firstOrFail();
        $correctIds = Redis::smembers("game:{$roomCode}:correct_guessers");
        $drawerId = Redis::get("game:{$roomCode}:current_drawer_id");

        foreach ($game->players as $p) {
            if ($p->id != $drawerId && !in_array((string) $p->id, $correctIds)) {
                $p->update(['streak' => 0]);
            }
        }

        Redis::del("game:{$roomCode}:current_word");
        Redis::del("game:{$roomCode}:current_drawer_id");
        Redis::del("game:{$roomCode}:round_ends_at");

        event(new RoundEnded($roomCode, $word, $reason));

        $this->advanceTurn($roomCode);
    }

    private function advanceTurn(string $roomCode): void
    {
        $game = Game::where('room_code', $roomCode)->firstOrFail();
        $order = json_decode(Redis::get("game:{$roomCode}:turn_order"), true);
        $index = (int) Redis::get("game:{$roomCode}:turn_index");

        $nextIndex = $index + 1;

        if ($nextIndex >= count($order)) {
            // everyone's had a turn this round — check if the game should end
            $round = (int) Redis::get("game:{$roomCode}:round");
            $roundsPerPlayer = $game->rounds_per_player;

            if ($round >= $roundsPerPlayer) {
                $this->endGame($game);
                return;
            }

            // start a new round: reset index, bump round number
            $nextIndex = 0;
            Redis::incr("game:{$roomCode}:round");
        }

        Redis::set("game:{$roomCode}:turn_index", $nextIndex);

        // small delay so players can read "the word was X" before the next turn starts
        \App\Jobs\StartNextTurn::dispatch($roomCode)->delay(now()->addSeconds(4));
    }

    private function endGame(Game $game): void
    {
        $scores = $this->getScores($game->room_code);

        foreach ($game->players as $p) {
            $p->update(['final_score' => $scores[$p->id] ?? 0]);
        }

        $game->update(['status' => 'finished']);
        event(new \App\Events\GameEnded($game->room_code));

        $this->cleanupGameKeys($game->room_code, $game->players->pluck('id')->all());
    }

    public function calculatePoints(string $roomCode, int $playerId, int $streak): int
    {
        $endsAt = (int) Redis::get("game:{$roomCode}:round_ends_at");
        $startedAt = $endsAt - self::ROUND_SECONDS;
        $elapsed = max(0, now()->timestamp - $startedAt);
        $points = max(10, 100 - $elapsed);

        $token = (int) Redis::get("game:{$roomCode}:round_token");
        $usedClue = Redis::exists("game:{$roomCode}:clue_reveal_count:{$playerId}:{$token}");
        if ($usedClue) {
            $points = min($points, 50);
        }

        $multiplier = $streak > 1 ? 1 + min($streak, 5) * 0.1 : 1;
        return (int) round($points * $multiplier);
    }

    public function addScore(string $roomCode, int $playerId, int $points): void
    {
        Redis::hincrby("game:{$roomCode}:scores", $playerId, $points);
    } 

    public function getScores(string $roomCode): array
    {
        return Redis::hgetall("game:{$roomCode}:scores");
    }

    private function cleanupGameKeys(string $roomCode, array $playerIds = []): void
    {
        Redis::del(
            "game:{$roomCode}:turn_order",
            "game:{$roomCode}:turn_index",
            "game:{$roomCode}:round",
            "game:{$roomCode}:round_token",
            "game:{$roomCode}:scores",
            "game:{$roomCode}:correct_guessers",
        );
    }

    public function buildHint(string $word, int $revealCount): string
    {
        $wordLength = mb_strlen($word);
        $maxRevealable = max(1, $wordLength - 1); // never reveal the very last letter
        $revealCount = min($revealCount, $maxRevealable);

        return strtoupper(mb_substr($word, 0, $revealCount)) . str_repeat('_', $wordLength - $revealCount);
    }

    public function autoRevealLetter(string $roomCode, int $token): void
    {
        $currentToken = (int) Redis::get("game:{$roomCode}:round_token");
        if ($token !== $currentToken) {
            return; // stale — same guard EndRound uses, this round already ended or moved on
        }

        $word = Redis::get("game:{$roomCode}:current_word");
        if (!$word) {
            return;
        }

        $count = (int) Redis::incr("game:{$roomCode}:auto_reveal_count");
        $hint = $this->buildHint($word, $count);

        event(new LetterAutoRevealed($roomCode, $hint));
    }
}