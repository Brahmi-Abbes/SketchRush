<?php

namespace App\Services;

use App\Events\RoundStarted;
use App\Events\TurnAwaitingWord;
use App\Events\WordChoicesOffered;
use App\Models\Game;
use App\Models\GamePlayer;
use Illuminate\Support\Facades\Redis;

class GameStateService
{
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
        Redis::del("game:{$game->room_code}:current_word");

        event(new WordChoicesOffered($drawer, $choices));
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
        Redis::del("game:{$game->room_code}:pending_choices");

        event(new RoundStarted($game->room_code, $player->guest_name));
    }
}