<?php

namespace App\Filament\Resources\Guru\Schemas;

use App\Enums\JenisKelamin;
use App\Filament\Forms\AturanFormRequest;
use App\Filament\Forms\Components\AkunPortalSection;
use App\Filament\Forms\Components\KelasAktifSelect;
use App\Http\Requests\DataMaster\GuruRequest;
use App\Models\Guru;
use App\Models\Kelas;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

/**
 * Form Guru (PRD 7.2, 7.4). Aturan validasi: App\Http\Requests\DataMaster\GuruRequest.
 */
class GuruForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Data Guru')
                    ->columns(2)
                    ->schema(AturanFormRequest::terapkan([
                        TextInput::make('nip')
                            ->label('NIP')
                            // Kosong disimpan NULL agar index unik tidak bentrok antar-Guru tanpa NIP.
                            ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? $state : null),
                        TextInput::make('nama')->label('Nama Guru'),
                        Select::make('jenis_kelamin')->label('Jenis Kelamin')->options(JenisKelamin::class),
                        TextInput::make('no_telepon')->label('No. Telepon')->type('tel'),
                        KelasAktifSelect::make('kelas')
                            ->label('Kelas yang Diajar')
                            ->multiple()
                            ->relationship('kelas', 'nama_kelas', self::urutkanKelas(...))
                            ->columnSpanFull(),
                        Textarea::make('alamat')->label('Alamat')->columnSpanFull(),
                    ], fn (?Guru $record = null): array => GuruRequest::aturan($record), GuruRequest::pesan())),
                AkunPortalSection::make(),
            ]);
    }

    /**
     * Pilihan Kelas terurut Tingkat → Jurusan → Nama Kelas (PRD 7.2).
     *
     * @param  Builder<Kelas>  $query
     * @return Builder<Kelas>
     */
    private static function urutkanKelas(Builder $query): Builder
    {
        return $query->urutanTampilan();
    }
}
