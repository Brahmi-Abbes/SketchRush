<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GamePlayer extends Model
{
    protected $fillable = [
        'game_id',
        'guest_name',
        'session_id',
        'final_score',
        'turns_taken',
    ];
    public function game(){
        return $this->belongsTo(Game::class);
    }
}
