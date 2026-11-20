<?php

namespace App\Filament\Resources\Guru\Tables;

use App\Filament\Forms\Components\KelasAktifSelect;
use App\Models\Guru;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Tabel Guru (PRD 7.3): urut Nama Guru A–Z; Guru dengan banyak Kelas tetap satu baris.
 */
class GuruTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('nama')
            ->columns([
                TextColumn::make('nip')->label('NIP')->placeholder('-'),
                TextColumn::make('nama')->label('Nama Guru'),
                TextColumn::make('jenis_kelamin')->label('Jenis Kelamin'),
                TextColumn::make('alamat')->label('Alamat')->wrap(),
                TextColumn::make('no_telepon')->label('No. Telepon'),
                TextColumn::make('kelas.nama_kelas')->label('Kelas yang Diajar')->badge()->placeholder('-'),
            ])
            ->emptyStateHeading('Belum ada data Guru.')
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                // Seluruh relasi guru_kelas dilepas oleh model saat masuk Trash (Guru::booted).
                DeleteAction::make(),
                // Guru tidak boleh aktif kembali tanpa minimal satu Kelas (PRD 7.6).
                RestoreAction::make()
                    ->modalDescription('Pilih minimal satu Kelas aktif untuk Guru ini sebelum dipulihkan.')
                    ->schema([KelasAktifSelect::make('kelas')->label('Kelas yang Diajar')->multiple()])
                    ->using(fn (Guru $record, array $data): bool => $record->pulihkanKeKelas(array_map(intval(...), $data['kelas']))),
            ]);
    }
}
