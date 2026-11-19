@props(['title'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} · {{ config('sekolah.nama_sistem') }}</title>

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.ts'])
</head>
<body class="min-h-dvh bg-canvas font-sans text-ink antialiased">
    <main class="flex min-h-dvh items-center justify-center px-4 py-10">
        <div class="flex w-full max-w-md flex-col gap-6">
            <x-molecules.brand class="self-center" />
            {{ $slot }}
        </div>
    </main>
</body>
</html>
