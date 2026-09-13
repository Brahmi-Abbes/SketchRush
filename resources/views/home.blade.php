<x-layout title="SketchRush">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <h1 class="font-display text-5xl font-semibold -rotate-1 inline-block">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" class="inline-block -mt-2 mr-1 text-coral">
                    <path d="M4 20l1-4L16 5l3 3L8 19l-4 1z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
                    <path d="M14 6.5l3 3" stroke="currentColor" stroke-width="1.6"/>
                </svg>
                SketchRush
            </h1>
            <svg width="200" height="14" viewBox="0 0 200 14" fill="none" class="mx-auto block -mt-1 text-coral">
                <path d="M3 9C40 2 75 12 110 5C140 -1 170 11 197 4" stroke="currentColor" stroke-width="4" stroke-linecap="round"/>
            </svg>
            <p class="mt-4 text-chalk/70 leading-relaxed">Draw for your friends. Guess what they're drawing. Whoever's fastest wins.</p>
        </div>

        <div class="panel bg-panel p-8 space-y-6">
            <form action="/rooms" method="POST" class="space-y-3">
                @csrf
                <label class="block text-sm text-chalk/60">Your name</label>
                <input type="text" name="guest_name" placeholder="e.g. Mohammad" required
                       class="w-full px-4 py-2.5 rounded-lg bg-ink border border-white/10 placeholder:text-chalk/30 focus:outline-none focus:border-coral focus:ring-2 focus:ring-coral/20 transition-shadow">
                <button type="submit"
                        class="w-full bg-coral hover:bg-coral-dark active:scale-[0.98] active:translate-y-px transition-all py-2.5 rounded-lg font-semibold text-ink focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-coral focus-visible:ring-offset-2 focus-visible:ring-offset-panel">
                    Start a room
                </button>
            </form>

            <div class="flex items-center gap-3 text-chalk/30 text-sm">
                <div class="h-px flex-1 bg-white/10"></div>
                already have a code?
                <div class="h-px flex-1 bg-white/10"></div>
            </div>

            <form action="/rooms/join" method="POST" class="space-y-3">
                @csrf
                <input type="text" name="guest_name" placeholder="Your name" required
                       class="w-full px-4 py-2.5 rounded-lg bg-ink border border-white/10 placeholder:text-chalk/30 focus:outline-none focus:border-teal focus:ring-2 focus:ring-teal/20 transition-shadow">
                <input type="text" name="room_code" placeholder="Room code" required
                       class="w-full px-4 py-2.5 rounded-lg bg-ink border border-white/10 placeholder:text-chalk/30 focus:outline-none focus:border-teal focus:ring-2 focus:ring-teal/20 transition-shadow uppercase tracking-widest">
                <button type="submit"
                        class="w-full bg-teal hover:bg-teal-dark active:scale-[0.98] active:translate-y-px transition-all py-2.5 rounded-lg font-semibold text-ink focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-teal focus-visible:ring-offset-2 focus-visible:ring-offset-panel">
                    Join room
                </button>
            </form>
        </div>
    </div>
</x-layout>