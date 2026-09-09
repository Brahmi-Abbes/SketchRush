<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'SketchRush' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-ink text-chalk font-sans min-h-screen flex items-center justify-center px-4 relative overflow-x-hidden">
    <div class="bg-grain pointer-events-none fixed inset-0" aria-hidden="true"></div>
    <div class="relative w-full flex items-center justify-center">
        {{ $slot }}
    </div>
</body>
</html>