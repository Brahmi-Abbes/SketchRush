<x-layout title="SketchRush">
    <div class="bg-gray-800 p-8 rounded-lg shadow-lg w-full max-w-md space-y-8">
        <h1 class="text-3xl font-bold text-center">SketchRush</h1>

        <form action="/rooms" method="POST" class="space-y-3">
            @csrf
            <input type="text" name="guest_name" placeholder="Your name" required
                   class="w-full px-4 py-2 rounded bg-gray-700 border border-gray-600 focus:outline-none focus:border-blue-500">
            <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 py-2 rounded font-semibold">
                Create Room
            </button>
        </form>

        <div class="border-t border-gray-700 pt-6 space-y-3">
            <form action="/rooms/join" method="POST" class="space-y-3">
                @csrf
                <input type="text" name="guest_name" placeholder="Your name" required
                       class="w-full px-4 py-2 rounded bg-gray-700 border border-gray-600 focus:outline-none focus:border-blue-500">
                <input type="text" name="room_code" placeholder="Room code" required
                       class="w-full px-4 py-2 rounded bg-gray-700 border border-gray-600 focus:outline-none focus:border-blue-500">
                <button type="submit"
                        class="w-full bg-gray-600 hover:bg-gray-700 py-2 rounded font-semibold">
                    Join Room
                </button>
            </form>
        </div>
    </div>
</x-layout>