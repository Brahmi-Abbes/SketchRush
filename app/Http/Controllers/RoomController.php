<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class RoomController extends Controller
{
    public function create(Request $request)
    {
        $roomCode = strtoupper(Str::random(6));
        $game = Game::create([
            'room_code' => $roomCode,
            'host_session_id' => session()->getId(),
            'status' => 'lobby',
        ]);

        $newPlayer = $game->players()->create([
            'guest_name' => $request->input('guest_name'),
            'session_id' => session()->getId(),
        ]);

        Auth::guard('players')->login($newPlayer);
        session(['guest_name' => $request->input('guest_name')]);

        return redirect()->route('rooms.show', ['code' => $roomCode]);
    }

    public function join(Request $request)
    {
        $game = Game::where('room_code', $request->input('room_code'))
            ->where('status', 'lobby')
            ->firstOrFail();

        $newPlayer = $game->players()->create([
            'guest_name' => $request->input('guest_name'),
            'session_id' => session()->getId(),
        ]);

        Auth::guard('players')->login($newPlayer);
        session(['guest_name' => $request->input('guest_name')]);

        return redirect()->route('rooms.show', ['code' => $game->room_code]);
    }
}