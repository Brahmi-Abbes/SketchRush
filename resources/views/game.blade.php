<x-layout title="Game — {{ $game->room_code }}">
    <span id="round-timer" class="text-lg font-mono"></span>
    <div id="game-app"
        data-room-code="{{ $game->room_code }}"
        data-is-drawer="{{ $isDrawer ? '1' : '0' }}"
        data-player-id="{{ auth('players')->id() }}"
        class="bg-gray-800 p-6 rounded-lg shadow-lg w-full max-w-2xl space-y-4">

        <div class="flex justify-between items-center">
            <h1 class="text-xl font-bold">Room: {{ $game->room_code }}</h1>
            <div id="status-text" class="text-gray-300">Waiting...</div>
        </div>

        @if($isDrawer && $pendingChoices)
            <div id="word-choices" class="space-y-2">
                @foreach($pendingChoices as $choice)
                    <button
                        class="word-choice-btn w-full bg-blue-600 hover:bg-blue-700 py-2 rounded"
                        data-word="{{ $choice['word'] }}">
                        {{ $choice['word'] }} <span class="text-xs text-gray-300">({{ $choice['category'] }})</span>
                    </button>
                @endforeach
            </div>
        @endif

        <canvas id="drawing-canvas" width="600" height="400"
                class="bg-white rounded w-full touch-none"></canvas>
        
        <div id="chat-log" class="bg-gray-900 rounded p-3 h-40 overflow-y-auto space-y-1 text-sm"></div>

        @if(!$isDrawer)
            <form id="guess-form" class="flex gap-2">
                <input id="guess-input" type="text" autocomplete="off" placeholder="Type your guess..."
                    class="flex-1 bg-gray-700 rounded px-3 py-2 text-sm" />
                <button type="submit" class="bg-green-600 hover:bg-green-700 px-4 py-2 rounded text-sm">Guess</button>
                @if(!$isDrawer && $cluesRemaining > 0)
                    <button id="clue-btn" class="bg-purple-600 hover:bg-purple-700 px-3 py-2 rounded text-sm">
                        Use Clue (<span id="clues-remaining">{{ $cluesRemaining }}</span> left)
                    </button>
                @endif
            </form>
        @endif

        <div id="drawer-controls" class="hidden">
            <button id="clear-canvas-btn" class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded text-sm">
                Clear Canvas
            </button>
        </div>
    </div>

    @vite(['resources/js/game.js'])
</x-layout>