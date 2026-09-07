<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Game extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_code',
        'host_session_id',
        'status',
        'rounds_per_player',
        'is_public',
    ];

    public function players()

    {
        return $this->hasMany(GamePlayer::class);
    }
}
