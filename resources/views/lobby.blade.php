@vite(['resources/js/lobby.js'])

<x-layout title="Lobby — {{ $game->room_code }}">
    <div id="lobby-app" data-room-code="{{ $game->room_code }}">
        <div class="bg-gray-800 p-8 rounded-lg shadow-lg w-full max-w-md space-y-6">
            <div class="text-center">
                <p class="text-gray-400 text-sm">Room Code</p>
                <h1 class="text-4xl font-bold tracking-widest">{{ $game->room_code }}</h1>
            </div>

            <button onclick="navigator.clipboard.writeText('{{ $game->room_code }}')"
                    class="text-xs text-blue-400 hover:text-blue-300 block mx-auto mt-1">
                Copy code
            </button>

            <div>
                <h2 class="text-lg font-semibold mb-2">Players</h2>
                <ul id="players-list" class="space-y-1">
                    @foreach($game->players as $player)
                        <li class="bg-gray-700 px-3 py-2 rounded">{{ $player->guest_name }}</li>
                    @endforeach
                </ul>
            </div>

            @if($game->host_session_id === session()->getId())
                <button id="start-btn"
                        disabled
                        class="w-full bg-green-600 hover:bg-green-700 disabled:bg-gray-600 disabled:cursor-not-allowed py-2 rounded font-semibold">
                    Start Game
                </button>
                <p id="start-hint" class="text-sm text-gray-400 text-center">Need at least 2 players</p>
            @else
                <p class="text-center text-gray-400">Waiting for host to start...</p>
            @endif
        </div>
    </div>
    
</x-layout>