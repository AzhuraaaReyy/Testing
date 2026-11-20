<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\Pages\Concerns\HalamanDataMaster;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Siswa;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Tables\Table;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Halaman daftar bersama untuk CRUD Siswa, Kelas, dan Guru:
 * - Tombol "Tambah <Data>" di kanan atas tabel (PRD 14.2).
 * - Tab Aktif / Trash (PRD 13.2).
 * - Halaman pagination diingat saat kembali dari Detail/Edit (PRD 14.8).
 * - Halaman yang tidak lagi valid (mis. setelah hapus) diarahkan ke halaman terakhir yang valid (PRD 14.8).
 */
abstract class ListDataMasterRecords extends ListRecords
{
    use HalamanDataMaster;

    /** Subjudul mengikuti tab yang sedang dibuka (Aktif / Trash). */
    protected function jenisHalaman(): string
    {
        return $this->activeTab === 'trash' ? 'trash' : 'index';
    }

    public function mount(): void
    {
        parent::mount();

        // Tanpa ?page di URL (mis. kembali lewat menu/breadcrumb/redirect), pakai halaman terakhir.
        // Diset langsung (bukan setPage()) karena tabel belum diinisialisasi saat mount.
        $page = session($this->tablePageSessionKey());

        if (! request()->query->has('page') && is_int($page) && $page > 1) {
            $this->paginators['page'] = $page;
        }
    }

    /**
     * Judul tabel terpisah dari header kolom dan tetap terlihat di semua state (PRD 5.3, 15.1, 14.9).
     */
    public function table(Table $table): Table
    {
        $label = static::getResource()::getModelLabel();

        return $table->heading(fn (): string => $this->activeTab === 'trash' ? "Trash {$label}" : "Daftar {$label}");
    }

    protected function getHeaderActions(): array
    {
        return [
            // Label "Tambah :label" dari lang/vendor/filament-actions/id/create.php.
            CreateAction::make(),
        ];
    }

    /**
     * @return array<string, Tab>
     */
    public function getTabs(): array
    {
        return [
            'aktif' => Tab::make('Aktif'),
            'trash' => Tab::make('Trash')->modifyQueryUsing(self::hanyaTrash(...)),
        ];
    }

    /**
     * @param  Builder<Siswa>|Builder<Kelas>|Builder<Guru>  $query
     * @return Builder<Siswa>|Builder<Kelas>|Builder<Guru>
     */
    private static function hanyaTrash(Builder $query): Builder
    {
        return $query->onlyTrashed();
    }

    /**
     * @param  Builder<Model>  $query
     * @return Paginator<int, Model>|CursorPaginator<int, Model>
     */
    protected function paginateTableQuery(Builder $query): Paginator|CursorPaginator
    {
        $records = parent::paginateTableQuery(clone $query);

        if ($records instanceof LengthAwarePaginator && $records->isEmpty() && $records->currentPage() > 1) {
            $this->setPage($records->lastPage(), $this->getTablePaginationPageName());
            $records = parent::paginateTableQuery($query);
        }

        session()->put($this->tablePageSessionKey(), $records instanceof LengthAwarePaginator ? $records->currentPage() : 1);

        return $records;
    }

    private function tablePageSessionKey(): string
    {
        return static::class.'.page.'.($this->activeTab ?? 'aktif');
    }
}
