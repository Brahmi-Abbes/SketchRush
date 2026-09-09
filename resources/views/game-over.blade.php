<x-layout title="Game Over — {{ $game->room_code }}">
    <div class="panel bg-panel p-8 w-full max-w-md space-y-6">
        <div class="text-center">
            <h1 class="font-display text-3xl -rotate-1 inline-block">That's a wrap!</h1>
            <svg width="160" height="12" viewBox="0 0 160 12" fill="none" class="mx-auto block mt-1 text-gold">
                <path d="M2 7C32 1 60 10 88 4C112 -1 138 9 158 3" stroke="currentColor" stroke-width="3.5" stroke-linecap="round"/>
            </svg>
        </div>

        <ol class="space-y-2">
            @foreach($leaderboard as $i => $p)
                <li class="flex items-center gap-3 rounded-lg px-4 py-3 {{ $i === 0 ? 'bg-gold text-ink' : 'bg-ink text-chalk border border-white/5' }}">
                    <span class="font-display text-lg w-6 text-center {{ $i === 0 ? '' : 'text-chalk/40' }}">{{ $i + 1 }}</span>
                    <span class="flex-1 font-semibold">{{ $p->guest_name }}</span>
                    <span class="font-display">{{ $p->final_score }}</span>
                </li>
            @endforeach
        </ol>

        <a href="{{ route('home') }}" class="block text-center bg-coral hover:bg-coral-dark active:scale-[0.98] transition-all text-ink font-semibold py-2.5 rounded-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-coral focus-visible:ring-offset-2 focus-visible:ring-offset-panel">
            Play again
        </a>
    </div>
</x-layout>