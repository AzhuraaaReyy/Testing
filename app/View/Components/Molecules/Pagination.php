<?php

namespace App\View\Components\Molecules;

use App\Support\Format;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\View\Component;

/**
 * Pagination standar seluruh tabel (PRD 15.2–15.4): informasi jumlah data di kiri,
 * kontrol nomor halaman dengan ellipsis di kanan; Sebelumnya/Berikutnya tetap terlihat
 * (redup) saat tidak dapat dipakai; seluruh elemen tetap tampil di mobile.
 *
 * Dua mode, satu tampilan:
 * - Tautan biasa (portal): diperkaya resources/js/components/table-region.ts.
 * - Livewire (panel Admin, :livewire="true"): klik memanggil gotoPage()/previousPage()/nextPage()
 *   komponen Livewire. Dipakai lewat override resources/views/vendor/filament/components/pagination.
 */
final class Pagination extends Component
{
    /**
     * Nomor halaman yang ditampilkan; null = ellipsis.
     *
     * @var list<int|null>
     */
    public readonly array $halaman;

    /**
     * @param  LengthAwarePaginator<int, mixed>  $paginator
     */
    public function __construct(
        public LengthAwarePaginator $paginator,
        public string $satuan = 'data',
        int $sisi = 1,
        public bool $livewire = false,
    ) {
        $this->halaman = self::jendela($paginator->currentPage(), $paginator->lastPage(), $sisi);
    }

    /**
     * Ekspresi wire:click untuk mode Livewire, mis. "gotoPage(3, 'page')".
     */
    public function aksiLivewire(string $metode, ?int $nomor = null): string
    {
        $namaHalaman = $this->paginator instanceof AbstractPaginator ? $this->paginator->getPageName() : 'page';

        return $nomor === null
            ? "{$metode}('{$namaHalaman}')"
            : "{$metode}({$nomor}, '{$namaHalaman}')";
    }

    /**
     * Halaman pertama & terakhir selalu tampil, ditambah $sisi halaman di kiri/kanan halaman aktif.
     * Celah satu halaman diisi nomornya langsung karena ellipsis tidak menghemat ruang.
     * Contoh (aktif 2 dari 20): [1, 2, 3, null, 20].
     *
     * @return list<int|null>
     */
    public static function jendela(int $aktif, int $terakhir, int $sisi = 1): array
    {
        $terlihat = collect([1, $terakhir, ...range(max(1, $aktif - $sisi), min($terakhir, $aktif + $sisi))])
            ->filter(fn (int $n): bool => $n >= 1 && $n <= $terakhir)
            ->unique()
            ->sort()
            ->values();

        $hasil = [];
        $sebelumnya = 0;

        foreach ($terlihat as $nomor) {
            $celah = $nomor - $sebelumnya;

            if ($celah === 2) {
                $hasil[] = $nomor - 1;
            } elseif ($celah > 2) {
                $hasil[] = null;
            }

            $hasil[] = $nomor;
            $sebelumnya = $nomor;
        }

        return $hasil;
    }

    public function ringkasan(): string
    {
        if ($this->paginator->total() === 0) {
            return "Menampilkan 0 dari 0 {$this->satuan}";
        }

        $awal = Format::angka((int) $this->paginator->firstItem());
        $akhir = Format::angka((int) $this->paginator->lastItem());
        $total = Format::angka($this->paginator->total());

        return "Menampilkan {$awal}–{$akhir} dari {$total} {$this->satuan}";
    }

    public function render(): View
    {
        return view('components.molecules.pagination');
    }
}
