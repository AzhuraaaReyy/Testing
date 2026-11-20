<?php

namespace App\Filament\Pages;

use App\Actions\Laporan\GetLaporanKelasAction;
use App\DTOs\Laporan\KelasRingkasanData;
use App\Enums\JenisLaporan;
use App\Enums\Role;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Page;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Locked;
use Livewire\WithPagination;

use function Filament\Support\original_request;

/**
 * Tiga laporan Admin (PRD Fitur 5–7) di dalam shell panel. Tabelnya Blade
 * (components/organisms/laporan-table, dipakai bersama portal), pagination via Livewire
 * sehingga loading hanya terjadi di area tabel (PRD 16.1–16.2).
 */
class Laporan extends Page
{
    use WithPagination;

    protected static ?string $slug = 'laporan/{jenis}';

    protected string $view = 'filament.pages.laporan';

    #[Locked]
    public JenisLaporan $jenis;

    public function mount(JenisLaporan $jenis): void
    {
        $this->jenis = $jenis;
    }

    /**
     * Satu item menu per jenis laporan, di grup "Laporan".
     *
     * @return array<NavigationItem>
     */
    public static function getNavigationItems(): array
    {
        return array_map(
            fn (JenisLaporan $jenis): NavigationItem => NavigationItem::make($jenis->labelMenu())
                ->group(Role::Admin->labelGrupLaporan())
                ->icon($jenis->icon()->heroicon())
                ->url(fn (): string => static::getUrl(['jenis' => $jenis]))
                ->activeWhen(fn (): bool => original_request()->routeIs(static::getRouteName())
                    && static::jenisDariRute() === $jenis),
            JenisLaporan::cases(),
        );
    }

    /**
     * Parameter {jenis} bisa sudah di-bind ke enum (SubstituteBindings) atau masih string.
     */
    private static function jenisDariRute(): ?JenisLaporan
    {
        $jenis = original_request()->route('jenis');

        return $jenis instanceof JenisLaporan ? $jenis : JenisLaporan::tryFrom((string) $jenis);
    }

    public function getTitle(): string
    {
        return $this->jenis->judul();
    }

    public function getSubheading(): string
    {
        return $this->jenis->deskripsi();
    }

    /**
     * @return array<string, string>
     */
    public function getBreadcrumbs(): array
    {
        return [Role::Admin->labelGrupLaporan(), $this->jenis->judul()];
    }

    /**
     * @return array{kelas: LengthAwarePaginator<int, KelasRingkasanData>}
     */
    protected function getViewData(): array
    {
        $laporan = app(GetLaporanKelasAction::class);
        $kelas = $laporan->handle($this->jenis, $this->getPage());

        // Halaman di luar jangkauan (mis. ?page=9) dibawa ke halaman terakhir yang valid (PRD 14.8).
        if ($kelas->currentPage() > $kelas->lastPage()) {
            $this->setPage($kelas->lastPage());
            $kelas = $laporan->handle($this->jenis, $kelas->lastPage());
        }

        return ['kelas' => $kelas];
    }
}
