{{--
    Halaman error HTTP berbahasa Indonesia (dipakai errors/4xx & errors/5xx).
    Teks: lang/id/kesalahan.php. Tautan kembali mengikuti peran pengguna (tamu: Login portal).

    @var \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $exception
--}}
@php
    $kode = $exception->getStatusCode();
    $teks = trans()->has("kesalahan.{$kode}") ? __("kesalahan.{$kode}") : __('kesalahan.'.($kode >= 500 ? '5xx' : '4xx'));
    $user = request()->user();
@endphp

<x-layouts.guest :title="$teks['judul']">
    <section class="flex flex-col items-center gap-4 rounded-lg border border-line bg-surface px-6 py-10 text-center shadow-raised">
        <span class="grid size-12 place-items-center rounded-full bg-danger-soft text-danger">
            <x-atoms.icon :icon="\App\Enums\Icon::TriangleAlert" />
        </span>
        <p class="text-sm font-semibold tracking-[0.12em] text-ink-muted tabular-nums">{{ $kode }}</p>
        <h1 class="text-2xl font-semibold tracking-tight text-ink">{{ $teks['judul'] }}</h1>
        <p class="max-w-sm text-base text-ink-muted">{{ $teks['pesan'] }}</p>
        <a
            href="{{ route($user?->role->homeRoute() ?? 'login') }}"
            class="mt-2 inline-flex min-h-11 items-center justify-center rounded-md bg-primary px-4 text-base font-semibold text-on-primary transition-colors duration-150 hover:bg-primary-hover"
        >
            {{ $user ? __('kesalahan.kembali') : __('kesalahan.login') }}
        </a>
    </section>
</x-layouts.guest>
