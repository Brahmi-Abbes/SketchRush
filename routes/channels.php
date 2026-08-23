<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('room.{roomCode}', function ($user, $roomCode) {
    return [
        'id' => $user->id,
        'name' => $user->guest_name,
    ];
}, ['guards' => ['players']]);