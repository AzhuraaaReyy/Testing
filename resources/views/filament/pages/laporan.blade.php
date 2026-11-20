{{--
    View App\Filament\Pages\Laporan. Tabel = komponen Blade yang sama dengan laporan portal.

    @var \Illuminate\Pagination\LengthAwarePaginator<int, \App\DTOs\Laporan\KelasRingkasanData> $kelas
--}}
<x-filament-panels::page>
    <x-organisms.table-card :title="$this->jenis->judulTabel()" id="tabel-laporan">
        {{--
            Loading hanya di area tabel; judul & struktur halaman tetap terlihat (PRD 16.1).
            [data-table-area]: diganti pesan error + "Coba Lagi" bila gagal dimuat (PRD 16.3).
        --}}
        <div
            data-table-area
            class="transition-opacity duration-150"
            wire:loading.class="pointer-events-none opacity-50"
            wire:target="gotoPage,previousPage,nextPage,setPage"
        >
            <x-organisms.laporan-table :jenis="$this->jenis" :kelas="$kelas" :caption="$this->jenis->judul()" />

            @if ($kelas->total() > 0)
                <div class="border-t border-line px-5 py-3.5">
                    <x-filament::pagination :paginator="$kelas" />
                </div>
            @endif
        </div>
    </x-organisms.table-card>
</x-filament-panels::page>
