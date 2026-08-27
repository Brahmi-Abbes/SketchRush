<x-layout title="Game — {{ $game->room_code }}">
    <div id="game-app"
         data-room-code="{{ $game->room_code }}"
         data-is-drawer="{{ $isDrawer ? '1' : '0' }}"
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

        <div id="drawer-controls" class="hidden">
            <button id="clear-canvas-btn" class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded text-sm">
                Clear Canvas
            </button>
        </div>
    </div>

    @vite(['resources/js/game.js'])
</x-layout>