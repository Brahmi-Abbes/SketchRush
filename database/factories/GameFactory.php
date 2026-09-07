<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class GameFactory extends Factory
{
    protected $model = \App\Models\Game::class;

    public function definition(): array
    {
        return [
            'room_code' => strtoupper($this->faker->bothify('??????')),
            'status' => 'playing',
            'host_session_id' => null,
            'rounds_per_player' => 3,
        ];
    }
}