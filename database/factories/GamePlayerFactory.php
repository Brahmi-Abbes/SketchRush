<?php

namespace Database\Factories;

use App\Models\Game;
use Illuminate\Database\Eloquent\Factories\Factory;

class GamePlayerFactory extends Factory
{
    protected $model = \App\Models\GamePlayer::class;

    public function definition(): array
    {
        return [
            'game_id' => Game::factory(),
            'guest_name' => $this->faker->firstName(),
            'session_id' => $this->faker->uuid(),
            'final_score' => 0,
            'turns_taken' => 0,
        ];
    }
}