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
        $validated = $request->validate([
            'guest_name' => 'required|string|max:20',
        ]);

        $roomCode = strtoupper(Str::random(6));
        $game = Game::create([
            'room_code' => $roomCode,
            'status' => 'lobby',
        ]);

        $newPlayer = $game->players()->create([
            'guest_name' => $validated['guest_name'],
            'session_id' => session()->getId(),
        ]);

        Auth::guard('players')->login($newPlayer);

        $game->update(['host_session_id' => $newPlayer->id]);

        return redirect()->route('rooms.show', ['code' => $roomCode]);
    }

    public function join(Request $request)
    {
        $validated = $request->validate([
            'guest_name' => 'required|string|max:20',
            'room_code' => 'required|string|size:6',
        ]);

        $game = Game::where('room_code', $validated['room_code'])
            ->where('status', 'lobby')
            ->firstOrFail();

        $newPlayer = $game->players()->create([
            'guest_name' => $validated['guest_name'],
            'session_id' => session()->getId(),
        ]);

        Auth::guard('players')->login($newPlayer);

        return redirect()->route('rooms.show', ['code' => $game->room_code]);
    }
    public function start(Request $request, string $code)
    {
        $game = Game::where('room_code', $code)->firstOrFail();

        $player = auth('players')->user();
        abort_unless($player, 403);
        abort_unless((string) $game->host_session_id === (string) $player->id, 403);
        abort_if($game->players()->count() < 2, 422, 'Need at least 2 players to start');

        $game->update(['status' => 'playing']);

        event(new GameStarted($game->room_code));

        return response()->noContent();
    }
    public function show(string $code)
    {
        $game = Game::where('room_code', $code)->with('players')->firstOrFail();

        if (! auth('players')->user()) {
            return redirect()->route('home');
        }

        return view('lobby', ['game' => $game]);
    }
}