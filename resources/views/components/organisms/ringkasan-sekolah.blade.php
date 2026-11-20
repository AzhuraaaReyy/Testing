@props([
    'ringkasan',
    'links' => [],
])

{{--
    Tiga kartu ringkasan (PRD 11.3, 17.4): 1 kolom mobile, 2 tablet, 3 desktop.
    Dipakai Dashboard Admin (Filament, kartu menjadi shortcut ke CRUD lewat $links)
    dan Dashboard portal (tanpa $links, kartu statis — PRD 22.3).

    @var \App\DTOs\Dashboard\RingkasanSekolahData $ringkasan
    @var array{siswa?: string, kelas?: string, guru?: string} $links
--}}
<section aria-label="Ringkasan" {{ $attributes->class(['grid gap-4 sm:grid-cols-2 lg:grid-cols-3']) }}>
    <x-molecules.stat-card label="Total Siswa" :value="$ringkasan->totalSiswa" :icon="\App\Enums\Icon::Users" :href="$links['siswa'] ?? null" />
    <x-molecules.stat-card label="Total Kelas" :value="$ringkasan->totalKelas" :icon="\App\Enums\Icon::Building" :href="$links['kelas'] ?? null" />
    <x-molecules.stat-card label="Total Guru" :value="$ringkasan->totalGuru" :icon="\App\Enums\Icon::Presentation" :href="$links['guru'] ?? null" />
</section>
