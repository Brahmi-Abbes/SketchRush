<?php

use App\Models\Game;
use App\Models\GamePlayer;

it('creates a game and makes the creator the host', function () {
    $response = $this->post('/rooms', ['guest_name' => 'Sam']);

    $game = Game::first();
    expect($game)->not->toBeNull();
    expect($game->status)->toBe('lobby');

    $player = GamePlayer::first();
    expect($player->guest_name)->toBe('Sam');
    expect($player->game_id)->toBe($game->id);
    expect((string) $game->host_session_id)->toBe((string) $player->id);

    $response->assertRedirect(route('rooms.show', ['code' => $game->room_code]));
    $this->assertAuthenticatedAs($player, 'players');
});

it('rejects room creation with a blank name', function () {
    $this->post('/rooms', ['guest_name' => ''])
        ->assertSessionHasErrors('guest_name');

    expect(Game::count())->toBe(0);
});