<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

Broadcast::channel('room.{roomCode}', function ($user, $roomCode) {
    dd('CALLBACK REACHED', $roomCode, session()->getId(), session('guest_name'));
    return ['id' => session()->getId(), 'name' => session('guest_name') ?? 'Unknown'];
});
