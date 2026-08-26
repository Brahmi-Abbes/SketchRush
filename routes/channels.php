<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('room.{roomCode}', function ($user, $roomCode) {
    return [
        'id' => $user->id,
        'name' => $user->guest_name,
    ];
}, ['guards' => ['players']]);

Broadcast::channel('player.{id}', function ($user, $id) {
    return (string) $user->id === (string) $id;
}, ['guards' => ['players']]);