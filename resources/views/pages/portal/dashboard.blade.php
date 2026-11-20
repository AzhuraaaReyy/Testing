{{--
    Dashboard portal Guru/Siswa (PRD v1.3): kartu ringkasan sama seperti Admin (PRD 11.3),
    tetapi statis karena portal tidak memiliki CRUD.

    @var \App\DTOs\Dashboard\RingkasanSekolahData $ringkasan
--}}
<x-layouts.app title="Beranda">
    <x-molecules.page-header title="Beranda" description="Ringkasan data Siswa, Kelas, dan Guru." />

    <x-organisms.ringkasan-sekolah :ringkasan="$ringkasan" />
</x-layouts.app>
