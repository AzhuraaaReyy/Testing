@props(['title'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-sidebar="expanded" data-drawer="closed">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} · {{ config('sekolah.nama_sistem') }}</title>

    {{-- Pulihkan state Sidebar sebelum render pertama agar tidak berkedip. Key = SIDEBAR_STORAGE_KEY di components/sidebar.ts. --}}
    <script>
        try {
            if (localStorage.getItem('sidebar') === 'collapsed') {
                document.documentElement.dataset.sidebar = 'collapsed';
            }
        } catch (e) {}
    </script>

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.ts'])
</head>
<body class="min-h-dvh bg-canvas font-sans text-ink antialiased">
    <a href="#konten" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-50 focus:rounded-md focus:bg-surface focus:px-4 focus:py-2 focus:shadow-overlay">
        Lewati ke konten utama
    </a>

    <x-organisms.navbar />

    <div class="flex">
        <x-organisms.sidebar />

        <main id="konten" tabindex="-1" class="min-w-0 flex-1 px-4 py-6 focus:outline-none sm:px-6 lg:px-8 lg:py-8">
            <div class="mx-auto flex max-w-7xl flex-col gap-6">
                {{-- Pesan informasi satu kali, mis. "Anda sedang login di portal…" (App\Filament\Pages\Auth\Login). --}}
                @if (session(\App\Filament\Pages\Auth\Login::FLASH_INFO))
                    <x-molecules.alert variant="info">{{ session(\App\Filament\Pages\Auth\Login::FLASH_INFO) }}</x-molecules.alert>
                @endif

                {{ $slot }}
            </div>
        </main>
    </div>

    <x-organisms.logout-dialog />
</body>
</html>
