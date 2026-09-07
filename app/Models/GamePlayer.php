<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class GamePlayer extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'game_id',
        'guest_name',
        'session_id',
        'final_score',
        'turns_taken',
        'clues_used',
        'streak',
    ];

    public function game()
    {
        return $this->belongsTo(Game::class);
    }
}