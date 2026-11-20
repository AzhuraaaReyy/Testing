<?php

namespace App\Filament\Widgets;

use App\Filament\Tables\Columns\NomorColumn;
use App\Models\Kelas;
use App\Support\Format;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Locked;

/**
 * Tabel distribusi per Kelas (PRD 11.4, 11.5). Satu widget untuk dua tabel:
 * DistribusiKelasWidget::make(['relasi' => 'siswa' | 'guru']).
 * Seluruh Kelas aktif tampil (jumlah 0 tetap tampil), tanpa pagination.
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
                TextColumn::make('tingkat')->label('Tingkat')->badge(),
                TextColumn::make('jurusan')->label('Jurusan')->placeholder('-'),
                TextColumn::make("{$relasi}_count")
                    ->label("Jumlah {$label}")
                    ->alignEnd()
                    ->formatStateUsing(fn (int $state): string => Format::angka($state)),
            ])
            ->paginated(false)
            ->emptyStateHeading('Belum ada data untuk ditampilkan.');
    }
}
