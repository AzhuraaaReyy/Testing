<?php

namespace App\Filament\Widgets;

use App\Filament\LabelBadge;
use App\Filament\Tables\Columns\NomorColumn;
use App\Models\Kelas;
use App\Support\Format;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\PaginationMode;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Locked;

/**
 * Tabel distribusi per Kelas (PRD 11.4, 11.5). Satu widget untuk dua tabel:
 * DistribusiKelasWidget::make(['relasi' => 'siswa' | 'guru']).
 * Seluruh Kelas aktif tampil (jumlah 0 tetap tampil), 10 Kelas per halaman seperti tabel lain
 * (PRD 15.2, bawaan Table::configureUsing di AdminPanelProvider).
 */
class DistribusiKelasWidget extends TableWidget
{
    /** Relasi Kelas yang dihitung: "siswa" atau "guru". */
    #[Locked]
    public string $relasi = 'siswa';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $label = $this->relasi === 'guru' ? 'Guru' : 'Siswa';
        $relasi = strtolower($label);

        return $table
            ->heading("Distribusi {$label} berdasarkan Kelas")
            // withCount memakai scope SoftDeletes: hanya Siswa/Guru aktif yang dihitung (PRD 12.3).
            ->query(fn (): Builder => Kelas::query()->withCount($relasi)->urutanTampilan())
            ->columns([
                NomorColumn::make(),
                TextColumn::make('nama_kelas')->label('Nama Kelas'),
                TextColumn::make('tingkat')->label('Tingkat')->formatStateUsing(LabelBadge::format(...))->html(),
                TextColumn::make('jurusan')->label('Jurusan')->placeholder('-'),
                TextColumn::make("{$relasi}_count")
                    ->label("Jumlah {$label}")
                    ->alignEnd()
                    ->formatStateUsing(fn (int $state): string => Format::angka($state)),
            ])
            // Dua tabel distribusi di satu halaman: parameter halaman di URL dipisah
            // (?distribusiSiswaPage=, ?distribusiGuruPage=) agar paging satu tabel tidak menggeser yang lain.
            ->queryStringIdentifier("distribusi{$label}")
            // Widget tabel Filament memakai pagination "simple" (tanpa total); komponen pagination
            // aplikasi butuh total data untuk "Menampilkan 1–10 dari N data" (PRD 15.2–15.4).
            ->paginationMode(PaginationMode::Default)
            ->emptyStateHeading('Belum ada data untuk ditampilkan.');
    }
}
