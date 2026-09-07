<?php

use App\Models\Game;
use App\Models\GamePlayer;
use App\Services\GameStateService;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;

beforeEach(function () {
    Redis::flushdb();
});

it('cycles to the next player without starting a new round', function () {
    Queue::fake();

    $game = Game::factory()->create(['rounds_per_player' => 3]);
    $players = GamePlayer::factory()->for($game)->count(3)->create();
    $ids = $players->pluck('id')->all();

    Redis::set("game:{$game->room_code}:turn_order", json_encode($ids));
    Redis::set("game:{$game->room_code}:turn_index", 0);
    Redis::set("game:{$game->room_code}:round", 1);
    Redis::set("game:{$game->room_code}:current_word", 'guitar');

    (new GameStateService())->endRound($game->room_code, 0, 'timeout');

    expect((int) Redis::get("game:{$game->room_code}:turn_index"))->toBe(1);
    expect((int) Redis::get("game:{$game->room_code}:round"))->toBe(1);
    expect($game->fresh()->status)->toBe('playing');
});

it('wraps into a new round after everyone has had a turn', function () {
    Queue::fake();

    $game = Game::factory()->create(['rounds_per_player' => 3]);
    $players = GamePlayer::factory()->for($game)->count(3)->create();
    $ids = $players->pluck('id')->all();

    Redis::set("game:{$game->room_code}:turn_order", json_encode($ids));
    Redis::set("game:{$game->room_code}:turn_index", 2); // last player's turn
    Redis::set("game:{$game->room_code}:round", 1);
    Redis::set("game:{$game->room_code}:current_word", 'guitar');

    (new GameStateService())->endRound($game->room_code, 0, 'timeout');

    expect((int) Redis::get("game:{$game->room_code}:turn_index"))->toBe(0);
    expect((int) Redis::get("game:{$game->room_code}:round"))->toBe(2);
});

it('ends the game after the final round instead of starting a new one', function () {
    Queue::fake();

    $game = Game::factory()->create(['rounds_per_player' => 2]);
    $players = GamePlayer::factory()->for($game)->count(3)->create();
    $ids = $players->pluck('id')->all();

    Redis::set("game:{$game->room_code}:turn_order", json_encode($ids));
    Redis::set("game:{$game->room_code}:turn_index", 2); // last player, last round
    Redis::set("game:{$game->room_code}:round", 2);
    Redis::set("game:{$game->room_code}:current_word", 'guitar');
    
    (new GameStateService())->endRound($game->room_code, 0, 'timeout');

    expect($game->fresh()->status)->toBe('finished');
});