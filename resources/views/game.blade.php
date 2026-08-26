<x-layout title="Game — {{ $game->room_code }}">
    <div id="game-app"
         data-room-code="{{ $game->room_code }}"
         data-is-drawer="{{ $isDrawer ? '1' : '0' }}"
         class="bg-gray-800 p-8 rounded-lg shadow-lg w-full max-w-md space-y-6">

        <div class="text-center">
            <p class="text-gray-400 text-sm">Room</p>
            <h1 class="text-2xl font-bold">{{ $game->room_code }}</h1>
        </div>

        <div id="status-text" class="text-center text-lg">Waiting...</div>

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
    </div>

    @vite(['resources/js/game.js'])
</x-layout>