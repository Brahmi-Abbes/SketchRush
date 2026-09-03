<x-layout title="Game — {{ $game->room_code }}">
    <div id="game-app"
        data-room-code="{{ $game->room_code }}"
        data-is-drawer="{{ $isDrawer ? '1' : '0' }}"
        data-player-id="{{ auth('players')->id() }}"
        class="bg-panel rounded-2xl p-6 w-full max-w-2xl space-y-4">

        <div class="flex justify-between items-center gap-4">
            <h1 class="font-display text-lg tracking-wide">Room {{ $game->room_code }}</h1>
            <div class="flex items-center gap-3">
                <div id="status-text" class="text-chalk/60 text-sm">Waiting...</div>
                <div class="relative w-11 h-11 shrink-0">
                    <svg viewBox="0 0 56 56" class="w-11 h-11 -rotate-90">
                        <circle cx="28" cy="28" r="24" fill="none" stroke="currentColor" stroke-width="4" class="text-white/10" />
                        <circle id="timer-ring" cx="28" cy="28" r="24" fill="none" stroke="currentColor" stroke-width="4"
                                class="text-coral" stroke-linecap="round"
                                stroke-dasharray="150.8" stroke-dashoffset="0" />
                    </svg>
                    <span id="round-timer" class="absolute inset-0 flex items-center justify-center font-display text-xs"></span>
                </div>
            </div>
        </div>
        @if(!$isDrawer && $streak > 1)
            <div class="text-gold text-sm font-semibold">🔥 {{ $streak }} in a row</div>
        @endif

        @if($isDrawer && $pendingChoices)
            <div id="word-choices" class="space-y-2">
                @foreach($pendingChoices as $choice)
                    <button
                        class="word-choice-btn w-full bg-coral hover:bg-coral-dark transition-colors text-ink font-semibold py-2.5 rounded-lg"
                        data-word="{{ $choice['word'] }}">
                        {{ $choice['word'] }} <span class="font-normal text-ink/60">— {{ $choice['category'] }}</span>
                    </button>
                @endforeach
            </div>
        @endif

        <canvas id="drawing-canvas" width="600" height="400"
                class="bg-white rounded-xl w-full touch-none"></canvas>

        <div id="chat-log" class="bg-ink rounded-xl p-3 h-40 overflow-y-auto space-y-1 text-sm"></div>

        @if(!$isDrawer)
            <form id="guess-form" class="flex gap-2">
                <input id="guess-input" type="text" autocomplete="off" placeholder="Type your guess..."
                    class="flex-1 bg-ink border border-white/10 rounded-lg px-3 py-2 text-sm placeholder:text-chalk/30 focus:outline-none focus:border-teal" />
                <button type="submit" class="bg-teal hover:bg-teal-dark transition-colors text-ink font-semibold px-4 py-2 rounded-lg text-sm">Guess</button>
                @if($cluesRemaining > 0)
                    <button id="clue-btn" type="button" class="bg-gold hover:brightness-95 transition text-ink font-semibold px-3 py-2 rounded-lg text-sm whitespace-nowrap">
                        Clue (<span id="clues-remaining">{{ $cluesRemaining }}</span>)
                    </button>
                @endif
            </form>
        @endif

        <div id="drawer-controls" class="hidden">
            <button id="clear-canvas-btn" class="border border-white/15 text-chalk/60 hover:border-coral hover:text-coral transition-colors px-4 py-2 rounded-lg text-sm">
                Clear canvas
            </button>
        </div>
    </div>

    @vite(['resources/js/game.js'])
</x-layout>