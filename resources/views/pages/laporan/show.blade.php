{{--
    Satu view untuk tiga laporan (PRD Fitur 5–7). Perbedaan kolom ditentukan oleh App\Enums\JenisLaporan.

    @var \App\Enums\JenisLaporan $jenis
    @var \App\Enums\Role $role
    @var \Illuminate\Pagination\LengthAwarePaginator<int, \App\DTOs\Laporan\KelasRingkasanData> $kelas
--}}

<x-layouts.app :title="$jenis->judul($role)">
    <x-molecules.page-header :eyebrow="$role->labelGrupLaporan()" :title="$jenis->judul($role)" :description="$jenis->deskripsi($role)" />

    <x-organisms.table-card :title="$jenis->judulTabel()" id="tabel-laporan">
        <x-organisms.table-region :fragment="\App\Http\PartialResponse::TABEL" :label="$jenis->judulTabel()">
            @fragment(\App\Http\PartialResponse::TABEL)
                <x-organisms.data-table :caption="$jenis->judul($role)">
                    <x-slot:head>
                        <x-atoms.th align="right" class="w-16">No.</x-atoms.th>
                        <x-atoms.th class="min-w-36">{{ $jenis->labelKolomKelas() }}</x-atoms.th>
                        <x-atoms.th class="w-24">Tingkat</x-atoms.th>
                        <x-atoms.th class="min-w-28">Jurusan</x-atoms.th>
                        @if ($jenis->menampilkanSiswa())
                            <x-atoms.th class="min-w-72">Daftar Siswa</x-atoms.th>
                        @endif
                        @if ($jenis->menampilkanGuru())
                            <x-atoms.th class="min-w-64">Daftar Guru</x-atoms.th>
                        @endif
                    </x-slot:head>

                    @forelse ($kelas as $baris)
                        <tr class="transition-colors duration-150 hover:bg-subtle/60">
                            <x-atoms.td numeric class="text-ink-muted">{{ \App\Support\Format::angka($kelas->firstItem() + $loop->index) }}</x-atoms.td>
                            <x-atoms.td class="font-semibold whitespace-nowrap">{{ $baris->namaKelas }}</x-atoms.td>
                            <x-atoms.td><x-atoms.tingkat-badge :tingkat="$baris->tingkat" /></x-atoms.td>
                            <x-atoms.td class="whitespace-nowrap">{{ $baris->jurusan ?? '-' }}</x-atoms.td>
                            @if ($jenis->menampilkanSiswa())
                                <x-atoms.td><x-molecules.name-list :names="$baris->siswa" /></x-atoms.td>
                            @endif
                            @if ($jenis->menampilkanGuru())
                                <x-atoms.td><x-molecules.name-list :names="$baris->guru" /></x-atoms.td>
                            @endif
                        </tr>
                    @empty
                        <x-molecules.table-empty :colspan="$jenis->jumlahKolom()" message="Tidak ada data kelas yang tersedia untuk ditampilkan." />
                    @endforelse
                </x-organisms.data-table>

                @if ($kelas->total() > 0)
                    <x-molecules.pagination :paginator="$kelas" />
                @endif
            @endfragment
        </x-organisms.table-region>
    </x-organisms.table-card>
</x-layouts.app>
