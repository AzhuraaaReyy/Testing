<?php

namespace App\Filament\Resources\Siswa\Tables;

use App\Filament\Forms\Components\KelasAktifSelect;
use App\Models\Siswa;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Tabel Siswa (PRD 5.3): urut Nama Siswa A–Z, tanpa pencarian.
 */
class SiswaTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('nama')
            ->columns([
                TextColumn::make('nis')->label('NIS'),
                TextColumn::make('nama')->label('Nama Siswa'),
                TextColumn::make('jenis_kelamin')->label('Jenis Kelamin'),
                TextColumn::make('alamat')->label('Alamat')->wrap(),
                TextColumn::make('kelas.nama_kelas')->label('Kelas')->placeholder('-'),
            ])
            ->emptyStateHeading('Belum ada data Siswa.')
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                // Relasi Kelas dilepas oleh model saat masuk Trash (Siswa::booted).
                DeleteAction::make(),
                // Siswa tidak boleh aktif kembali tanpa Kelas (PRD 5.7).
                RestoreAction::make()
                    ->modalDescription('Pilih Kelas aktif untuk Siswa ini sebelum dipulihkan.')
                    ->schema([KelasAktifSelect::make('kelas_id')])
                    ->using(fn (Siswa $record, array $data): bool => $record->pulihkanKeKelas((int) $data['kelas_id'])),
            ]);
    }
}
