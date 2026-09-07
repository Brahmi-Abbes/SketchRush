<?php

use App\Models\Game;
use App\Models\GamePlayer;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;

beforeEach(function () {
    Redis::flushdb();
});

function setupActiveRound(Game $game, GamePlayer $drawer, string $word): void
{
    Redis::set("game:{$game->room_code}:turn_order", json_encode([$drawer->id]));
    Redis::set("game:{$game->room_code}:turn_index", 0);
    Redis::set("game:{$game->room_code}:round", 1);
    Redis::set("game:{$game->room_code}:current_drawer_id", $drawer->id);
    Redis::set("game:{$game->room_code}:current_word", $word);
    Redis::set("game:{$game->room_code}:round_ends_at", now()->addSeconds(80)->timestamp);
    Redis::set("game:{$game->room_code}:round_token", 1);
}

it('accepts a correct guess case-insensitively', function () {
    Queue::fake();

    $game = Game::factory()->create(['room_code' => 'ABC123']);
    $drawer = GamePlayer::factory()->for($game)->create();
    $guesser = GamePlayer::factory()->for($game)->create();

    setupActiveRound($game, $drawer, 'Guitar');

    $response = $this->actingAs($guesser, 'players')
        ->postJson("/rooms/{$game->room_code}/guess", ['guess' => 'guitar']);

    $response->assertNoContent();
    expect(Redis::sismember("game:{$game->room_code}:correct_guessers", $guesser->id))->toBeTruthy();
});

it('does not let the same player score twice for one round', function () {
    Queue::fake();

    $game = Game::factory()->create(['room_code' => 'ABC123']);
    $drawer = GamePlayer::factory()->for($game)->create();
    $guesser = GamePlayer::factory()->for($game)->create();

    setupActiveRound($game, $drawer, 'Guitar');

    $this->actingAs($guesser, 'players')->postJson("/rooms/{$game->room_code}/guess", ['guess' => 'guitar']);
    $scoreAfterFirst = Redis::hget("game:{$game->room_code}:scores", $guesser->id);

    $this->actingAs($guesser, 'players')->postJson("/rooms/{$game->room_code}/guess", ['guess' => 'guitar']);
    $scoreAfterSecond = Redis::hget("game:{$game->room_code}:scores", $guesser->id);

    expect($scoreAfterSecond)->toBe($scoreAfterFirst);
});

it('rejects a guess from the drawer', function () {
    $game = Game::factory()->create(['room_code' => 'ABC123']);
    $drawer = GamePlayer::factory()->for($game)->create();

    setupActiveRound($game, $drawer, 'Guitar');

    $this->actingAs($drawer, 'players')
        ->postJson("/rooms/{$game->room_code}/guess", ['guess' => 'guitar'])
        ->assertForbidden();
});