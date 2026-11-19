@props([
    'message' => 'Data gagal dimuat. Silakan coba lagi.',
])

{{--
    Error state dibedakan dari empty state (PRD 16.3).
    Pesan default untuk error server (5xx/jaringan); untuk 422 klien mengganti isi [data-error-message]
    dengan pesan validasi dari server.
--}}
<div role="alert" class="flex flex-col items-center gap-4 px-6 py-14 text-center">
    <span class="grid size-11 place-items-center rounded-full bg-danger-soft text-danger">
        <x-atoms.icon :icon="\App\Enums\Icon::TriangleAlert" />
    </span>
    <p class="max-w-sm text-base font-semibold text-ink" data-error-message>{{ $message }}</p>
    <x-atoms.button :icon="\App\Enums\Icon::RotateCw" data-region-retry>Coba Lagi</x-atoms.button>
</div>
