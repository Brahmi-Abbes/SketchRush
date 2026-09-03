<x-layout title="SketchRush">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <h1 class="font-display text-5xl font-semibold -rotate-1 inline-block">SketchRush</h1>
            <p class="mt-3 text-chalk/70">Draw for your friends. Guess what they're drawing. Whoever's fastest wins.</p>
        </div>

        <div class="bg-panel rounded-2xl p-8 space-y-6">
            <form action="/rooms" method="POST" class="space-y-3">
                @csrf
                <label class="block text-sm text-chalk/60">Your name</label>
                <input type="text" name="guest_name" placeholder="e.g. Sam" required
                       class="w-full px-4 py-2.5 rounded-lg bg-ink border border-white/10 placeholder:text-chalk/30 focus:outline-none focus:border-coral">
                <button type="submit"
                        class="w-full bg-coral hover:bg-coral-dark transition-colors py-2.5 rounded-lg font-semibold text-ink">
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
                       class="w-full px-4 py-2.5 rounded-lg bg-ink border border-white/10 placeholder:text-chalk/30 focus:outline-none focus:border-teal">
                <input type="text" name="room_code" placeholder="Room code" required
                       class="w-full px-4 py-2.5 rounded-lg bg-ink border border-white/10 placeholder:text-chalk/30 focus:outline-none focus:border-teal uppercase tracking-widest">
                <button type="submit"
                        class="w-full bg-teal hover:bg-teal-dark transition-colors py-2.5 rounded-lg font-semibold text-ink">
                    Join room
                </button>
            </form>
        </div>
    </div>
</x-layout>