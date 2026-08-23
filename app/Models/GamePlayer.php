<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;


class GamePlayer extends Authenticatable
{
    protected $fillable = [
        'game_id',
        'guest_name',
        'session_id',
        'final_score',
        'turns_taken',
    ];

    public function game()
    {
        return $this->belongsTo(Game::class);
    }
}
