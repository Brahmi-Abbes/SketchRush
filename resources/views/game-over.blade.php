<x-layout title="Game Over — {{ $game->room_code }}">
    <div class="bg-gray-800 p-6 rounded-lg shadow-lg w-full max-w-2xl space-y-4">
        <h1 class="text-2xl font-bold text-center">Game Over!</h1>

        <ol class="space-y-2">
            @foreach($leaderboard as $i => $p)
                <li class="flex justify-between items-center bg-gray-700 rounded px-4 py-3">
                    <span class="font-semibold">
                        {{ $i + 1 }}. {{ $p->guest_name }}
                        @if($i === 0) 🏆 @endif
                    </span>
                    <span class="font-mono">{{ $p->final_score }}</span>
                </li>
            @endforeach
        </ol>

        <a href="{{ route('home') }}" class="block text-center bg-blue-600 hover:bg-blue-700 py-2 rounded">
            Play Again
        </a>
    </div>
</x-layout>