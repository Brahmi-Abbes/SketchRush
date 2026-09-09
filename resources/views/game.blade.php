<x-layout title="Game — {{ $game->room_code }}">
    <div id="game-app"
        data-room-code="{{ $game->room_code }}"
        data-is-drawer="{{ $isDrawer ? '1' : '0' }}"
        data-player-id="{{ auth('players')->id() }}"
        class="panel bg-panel p-6 w-full max-w-2xl space-y-4">

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
        <div id="streak-badge" class="text-gold text-sm font-semibold {{ (!$isDrawer && $streak > 1) ? '' : 'hidden' }}">
            🔥 <span id="streak-count">{{ $streak }}</span> in a row
        </div>

        <div id="scoreboard" class="flex flex-wrap gap-2 text-sm">
            @foreach($game->players as $p)
                <div class="flex items-center gap-1.5 bg-ink px-2.5 py-1 rounded-full border border-white/5">
                    <span class="text-chalk/70">{{ $p->guest_name }}</span>
                    <span id="score-{{ $p->id }}" class="font-display text-gold">{{ $scores[$p->id] ?? 0 }}</span>
                </div>
            @endforeach
        </div>

        @if($isDrawer && $pendingChoices)
            <div id="word-choices" class="space-y-2">
                @foreach($pendingChoices as $choice)
                    <button
                        class="word-choice-btn w-full bg-coral hover:bg-coral-dark active:scale-[0.99] transition-all text-ink font-semibold py-2.5 rounded-lg"
                        data-word="{{ $choice['word'] }}">
                        {{ $choice['word'] }} <span class="font-normal text-ink/60">— {{ $choice['category'] }}</span>
                    </button>
                @endforeach
            </div>
        @endif

        <div class="p-2 rounded-2xl bg-gradient-to-br from-frame to-[#8f6a3f] shadow-[0_20px_45px_-20px_rgba(0,0,0,0.6)]">
            <canvas id="drawing-canvas" width="600" height="400"
                    class="bg-white rounded-xl w-full touch-none block"></canvas>
        </div>

        <div id="chat-log" class="bg-ink rounded-xl p-3 h-40 overflow-y-auto space-y-1 text-sm border border-white/5"></div>

        @if(!$isDrawer)
            <form id="guess-form" class="flex gap-2">
                <input id="guess-input" type="text" autocomplete="off" placeholder="Type your guess..."
                    class="flex-1 bg-ink border border-white/10 rounded-lg px-3 py-2 text-sm placeholder:text-chalk/30 focus:outline-none focus:border-teal focus:ring-2 focus:ring-teal/20 transition-shadow" />
                <button type="submit" class="bg-teal hover:bg-teal-dark active:scale-[0.97] transition-all text-ink font-semibold px-4 py-2 rounded-lg text-sm">Guess</button>
                @if($cluesRemaining > 0)
                    <button id="clue-btn" type="button" class="bg-gold hover:brightness-95 active:scale-[0.97] transition-all text-ink font-semibold px-3 py-2 rounded-lg text-sm whitespace-nowrap">
                        Clue (<span id="clues-remaining">{{ $cluesRemaining }}</span>)
                    </button>
                @endif
            </form>
        @endif

        <div id="drawer-controls" class="hidden">
            <button id="clear-canvas-btn" class="border border-white/15 text-chalk/60 hover:border-coral hover:text-coral active:scale-[0.98] transition-all px-4 py-2 rounded-lg text-sm">
                Clear canvas
            </button>
        </div>
    </div>

    @vite(['resources/js/game.js'])
</x-layout>