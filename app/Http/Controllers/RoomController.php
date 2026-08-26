<?php

namespace App\Http\Controllers;

use App\Events\GameStarted;
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
            'status' => 'lobby',
        ]);

        $newPlayer = $game->players()->create([
            'guest_name' => $request->input('guest_name'),
            'session_id' => session()->getId(),
        ]);

        Auth::guard('players')->login($newPlayer);

        $game->update(['host_session_id' => $newPlayer->id]);
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

    public function start(Request $request, $code)
    {
        $game = Game::where('room_code', $code)->firstOrFail();

        if ((string) $game->host_session_id !== (string) auth('players')->id()) {
            abort(403);
        }

        if ($game->players()->count() < 2) {
            abort(422, 'Need at least 2 players');
        }

        $game->update(['status' => 'playing']);
        event(new GameStarted($game->room_code));

        return response()->noContent();
    }
}