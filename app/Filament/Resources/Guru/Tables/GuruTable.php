<?php

namespace App\Filament\Resources\Guru\Tables;

use App\Filament\Forms\AturanFormRequest;
use App\Filament\Forms\Components\KelasAktifSelect;
use App\Filament\LabelBadge;
use App\Filament\Tables\Columns\NomorColumn;
use App\Http\Requests\DataMaster\GuruRequest;
use App\Models\Guru;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

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
                NomorColumn::make(),
                TextColumn::make('nip')->label('NIP')->placeholder('-'),
                // wrap()/wrapHeader(): tabel Guru paling lebar; teks & judul kolom panjang turun ke
                // baris berikutnya alih-alih membuat tabel perlu digeser horizontal.
                TextColumn::make('nama')->label('Nama Guru')->wrap(),
                TextColumn::make('jenis_kelamin')->label('Jenis Kelamin')->wrapHeader(),
                TextColumn::make('alamat')->label('Alamat')->wrap(),
                TextColumn::make('no_telepon')->label('No. Telepon')->wrapHeader(),
                // Badge Kelas berjajar tanpa koma (LabelBadge::daftar), sama dengan kolom Kelas di Siswa.
                TextColumn::make('kelas.nama_kelas')
                    ->label('Kelas yang Diajar')
                    ->state(fn (Guru $record): ?HtmlString => LabelBadge::daftar($record->kelas->pluck('nama_kelas')))
                    ->wrap()
                    ->wrapHeader()
                    ->placeholder('-'),
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
                    ->schema(AturanFormRequest::terapkan(
                        [KelasAktifSelect::make('kelas')->label('Kelas yang Diajar')->multiple()],
                        fn (): array => GuruRequest::aturanPemulihan(),
                    ))
                    ->using(fn (Guru $record, array $data): bool => $record->pulihkanKeKelas(array_values(array_map(intval(...), $data['kelas'])))),
            ]);
    }
}
