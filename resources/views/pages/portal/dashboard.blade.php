{{--
    Dashboard portal Guru/Siswa (PRD v1.3): kartu ringkasan sama seperti Admin (PRD 11.3),
    tetapi statis karena portal tidak memiliki CRUD.

    @var \App\DTOs\Dashboard\RingkasanSekolahData $ringkasan
--}}
<x-layouts.app title="Beranda">
    <x-molecules.page-header title="Beranda" description="Ringkasan data Siswa, Kelas, dan Guru." />

    <section aria-label="Ringkasan" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <x-molecules.stat-card label="Total Siswa" :value="$ringkasan->totalSiswa" :icon="\App\Enums\Icon::Users" />
        <x-molecules.stat-card label="Total Kelas" :value="$ringkasan->totalKelas" :icon="\App\Enums\Icon::Building" />
        <x-molecules.stat-card label="Total Guru" :value="$ringkasan->totalGuru" :icon="\App\Enums\Icon::Presentation" />
    </section>
</x-layouts.app>
