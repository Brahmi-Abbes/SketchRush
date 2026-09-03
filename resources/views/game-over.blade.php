<x-layout title="Game Over — {{ $game->room_code }}">
    <div class="bg-panel rounded-2xl p-8 w-full max-w-md space-y-6">
        <div class="text-center">
            <h1 class="font-display text-3xl -rotate-1 inline-block">That's a wrap!</h1>
        </div>

        <ol class="space-y-2">
            @foreach($leaderboard as $i => $p)
                <li class="flex items-center gap-3 rounded-lg px-4 py-3 {{ $i === 0 ? 'bg-gold text-ink' : 'bg-ink text-chalk' }}">
                    <span class="font-display text-lg w-6 text-center {{ $i === 0 ? '' : 'text-chalk/40' }}">{{ $i + 1 }}</span>
                    <span class="flex-1 font-semibold">{{ $p->guest_name }}</span>
                    <span class="font-display">{{ $p->final_score }}</span>
                </li>
            @endforeach
        </ol>

        <a href="{{ route('home') }}" class="block text-center bg-coral hover:bg-coral-dark transition-colors text-ink font-semibold py-2.5 rounded-lg">
            Play again
        </a>
    </div>
</x-layout>