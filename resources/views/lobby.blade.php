@vite(['resources/js/lobby.js'])

<x-layout title="Lobby — {{ $game->room_code }}">
    <div id="lobby-app" data-room-code="{{ $game->room_code }}" class="w-full max-w-md">
        <div class="panel bg-panel p-8 space-y-6">
            <div class="text-center">
                <p class="text-sm text-chalk/50 mb-2">Room code</p>
                <div class="inline-block border-4 border-dashed border-coral rounded-[24px_24px_24px_6px] px-6 py-3 -rotate-1">
                    <h1 class="font-display text-4xl tracking-[0.2em]">{{ $game->room_code }}</h1>
                </div>
                <button id="copy-code-btn" data-code="{{ $game->room_code }}"
                        class="flex items-center gap-1.5 mx-auto mt-3 text-sm text-teal hover:text-teal-dark border border-teal/30 hover:border-teal rounded-full px-3 py-1.5 transition-colors active:scale-[0.97]">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4">
                        <rect x="9" y="9" width="11" height="11" rx="2" />
                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" />
                    </svg>
                    <span id="copy-code-label">Copy code</span>
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
                        class="w-full bg-coral hover:bg-coral-dark active:scale-[0.98] disabled:active:scale-100 disabled:bg-white/10 text-ink disabled:text-chalk/50 disabled:cursor-not-allowed transition-all py-2.5 rounded-lg font-semibold focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-coral focus-visible:ring-offset-2 focus-visible:ring-offset-panel">
                    Start game
                </button>
                <p id="start-hint" class="text-sm text-chalk/40 text-center">Need at least 2 players to start</p>
            @else
                <p class="text-center text-chalk/50">Waiting for the host to start...</p>
            @endif
        </div>
    </div>
</x-layout>