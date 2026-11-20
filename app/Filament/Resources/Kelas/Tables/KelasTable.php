<?php

namespace App\Filament\Resources\Kelas\Tables;

use App\Filament\LabelBadge;
use App\Filament\Tables\Columns\NomorColumn;
use App\Models\Kelas;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Tabel Kelas (PRD 6.3): urut Tingkat → Jurusan → Nama Kelas, tanpa pencarian.
 */
class KelasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(self::urutkan(...))
            ->columns([
                NomorColumn::make(),
                TextColumn::make('nama_kelas')->label('Nama Kelas'),
                TextColumn::make('tingkat')->label('Tingkat')->formatStateUsing(LabelBadge::format(...))->html(),
                TextColumn::make('jurusan')->label('Jurusan')->placeholder('-'),
                TextColumn::make('keterangan')->label('Keterangan')->placeholder('-')->wrap(),
            ])
            ->emptyStateHeading('Belum ada data Kelas.')
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                // Penolakan dijaga model (Kelas::booted); di sini hanya menampilkan alasannya.
                DeleteAction::make()
                    ->failureNotificationTitle(fn (Kelas $record): string => $record->alasanTidakDapatDihapus() ?? 'Kelas gagal dihapus.'),
                // Relasi lama tidak dikembalikan otomatis (PRD 6.6).
                RestoreAction::make(),
            ]);
    }

    /**
     * @param  Builder<Kelas>  $query
     * @return Builder<Kelas>
     */
    private static function urutkan(Builder $query): Builder
    {
        return $query->urutanTampilan();
    }
}
