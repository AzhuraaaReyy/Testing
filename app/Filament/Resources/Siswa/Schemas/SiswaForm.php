<?php

namespace App\Filament\Resources\Siswa\Schemas;

use App\Enums\JenisKelamin;
use App\Filament\Forms\AturanFormRequest;
use App\Filament\Forms\Components\AkunPortalSection;
use App\Filament\Forms\Components\KelasAktifSelect;
use App\Http\Requests\DataMaster\SiswaRequest;
use App\Models\Siswa;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * Form Siswa (PRD 5.2, 5.4, 5.6). Aturan validasi: App\Http\Requests\DataMaster\SiswaRequest.
 */
class SiswaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Data Siswa')
                    ->columns(2)
                    ->schema(AturanFormRequest::terapkan([
                        TextInput::make('nis')->label('NIS'),
                        TextInput::make('nama')->label('Nama Siswa'),
                        Select::make('jenis_kelamin')->label('Jenis Kelamin')->options(JenisKelamin::class),
                        KelasAktifSelect::make('kelas_id'),
                        Textarea::make('alamat')->label('Alamat')->columnSpanFull(),
                    ], fn (?Siswa $record = null): array => SiswaRequest::aturan($record))),
                AkunPortalSection::make(),
            ]);
    }
}
