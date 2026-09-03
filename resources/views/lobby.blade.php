@vite(['resources/js/lobby.js'])

<x-layout title="Lobby — {{ $game->room_code }}">
    <div id="lobby-app" data-room-code="{{ $game->room_code }}" class="w-full max-w-md">
        <div class="bg-panel rounded-2xl p-8 space-y-6">
            <div class="text-center">
                <p class="text-sm text-chalk/50 mb-2">Room code</p>
                <div class="inline-block border-4 border-dashed border-coral rounded-2xl px-6 py-3 -rotate-1">
                    <h1 class="font-display text-4xl tracking-[0.2em]">{{ $game->room_code }}</h1>
                </div>
                <button onclick="navigator.clipboard.writeText('{{ $game->room_code }}')"
                        class="block mx-auto mt-3 text-sm text-teal hover:text-teal-dark">
                    Copy code
                </button>
            </div>

            <div>
                <h2 class="text-sm text-chalk/50 mb-3">{{ $game->players->count() }} in the room</h2>
                <ul id="players-list" class="space-y-2">
                    @foreach($game->players as $player)
                        <li class="flex items-center gap-3 bg-ink px-3 py-2.5 rounded-lg">
                            <span class="w-8 h-8 rounded-full bg-teal flex items-center justify-center text-ink font-semibold text-sm shrink-0">
                                {{ strtoupper(substr($player->guest_name, 0, 1)) }}
                            </span>
                            <span>{{ $player->guest_name }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            @if((string) $game->host_session_id === (string) auth('players')->id())
                <button id="start-btn"
                        disabled
                        class="w-full bg-coral hover:bg-coral-dark disabled:bg-white/10 text-ink disabled:text-chalk/30 disabled:cursor-not-allowed transition-colors py-2.5 rounded-lg font-semibold">
                    Start game
                </button>
                <p id="start-hint" class="text-sm text-chalk/40 text-center">Need at least 2 players to start</p>
            @else
                <p class="text-center text-chalk/50">Waiting for the host to start...</p>
            @endif
        </div>
    </div>
</x-layout>