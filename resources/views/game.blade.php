<x-layout title="Game — {{ $game->room_code }}">
    <div class="bg-gray-800 p-8 rounded-lg shadow-lg w-full max-w-md text-center">
        <h1 class="text-2xl font-bold">Game Screen</h1>
        <p class="text-gray-400 mt-2">Room: {{ $game->room_code }}</p>
    </div>
</x-layout>