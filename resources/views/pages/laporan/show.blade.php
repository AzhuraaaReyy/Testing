{{--
    Laporan portal Guru/Siswa (PRD 22.4). Laporan Admin memakai tabel yang sama di
    App\Filament\Pages\Laporan.

    @var \App\Enums\JenisLaporan $jenis
    @var \App\Enums\Role $role
    @var \Illuminate\Pagination\LengthAwarePaginator<int, \App\DTOs\Laporan\KelasRingkasanData> $kelas
--}}

<x-layouts.app :title="$jenis->judul($role)">
    <x-molecules.page-header :eyebrow="$role->labelGrupLaporan()" :title="$jenis->judul($role)" :description="$jenis->deskripsi($role)" />

    <x-organisms.table-card :title="$jenis->judulTabel()" id="tabel-laporan">
        <x-organisms.table-region :fragment="\App\Http\PartialResponse::TABEL" :label="$jenis->judulTabel()">
            @fragment(\App\Http\PartialResponse::TABEL)
                <x-organisms.laporan-table :jenis="$jenis" :kelas="$kelas" :caption="$jenis->judul($role)" />

                @if ($kelas->total() > 0)
                    <x-molecules.pagination :paginator="$kelas" />
                @endif
            @endfragment
        </x-organisms.table-region>
    </x-organisms.table-card>
</x-layouts.app>
