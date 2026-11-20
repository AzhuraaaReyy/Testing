<?php

namespace App\Filament\Resources\Siswa\Schemas;

use App\Enums\JenisKelamin;
use App\Filament\Forms\Components\AkunPortalSection;
use App\Filament\Forms\Components\KelasAktifSelect;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * Form Siswa (PRD 5.2, 5.4, 5.6). NIS berupa teks (nol di awal terjaga) dan unik
 * termasuk terhadap data di Trash (PRD 13.3).
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
                    ->schema([
                        TextInput::make('nis')
                            ->label('NIS')
                            ->required()
                            ->maxLength(30)
                            ->unique(ignoreRecord: true),
                        TextInput::make('nama')
                            ->label('Nama Siswa')
                            ->required()
                            ->maxLength(150),
                        Select::make('jenis_kelamin')
                            ->label('Jenis Kelamin')
                            ->options(JenisKelamin::class)
                            ->required(),
                        KelasAktifSelect::make('kelas_id'),
                        Textarea::make('alamat')
                            ->label('Alamat')
                            ->required()
                            ->columnSpanFull(),
                    ]),
                AkunPortalSection::make(),
            ]);
    }
}
