<?php

namespace App\Filament\Resources\Guru\Schemas;

use App\Enums\JenisKelamin;
use App\Filament\Forms\Components\AkunPortalSection;
use App\Filament\Forms\Components\KelasAktifSelect;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

/**
 * Form Guru (PRD 7.2, 7.4). NIP opsional, unik bila diisi (termasuk data di Trash);
 * No. Telepon format Indonesia 08… / +628….
 */
class GuruForm
{
    /** 08xxxxxxxx atau +628xxxxxxxx, total 10–15 digit. */
    public const POLA_TELEPON = '/^(\+62|0)8[0-9]{7,12}$/';

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Data Guru')
                    ->columns(2)
                    ->schema([
                        TextInput::make('nip')
                            ->label('NIP')
                            ->maxLength(30)
                            ->unique(ignoreRecord: true)
                            // Kosong disimpan NULL agar index unik tidak bentrok antar-Guru tanpa NIP.
                            ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? $state : null),
                        TextInput::make('nama')
                            ->label('Nama Guru')
                            ->required()
                            ->maxLength(150),
                        Select::make('jenis_kelamin')
                            ->label('Jenis Kelamin')
                            ->options(JenisKelamin::class)
                            ->required(),
                        TextInput::make('no_telepon')
                            ->label('No. Telepon')
                            ->tel()
                            ->required()
                            ->maxLength(20)
                            ->regex(self::POLA_TELEPON)
                            ->validationMessages(['regex' => 'Format No. Telepon tidak valid. Gunakan 08… atau +628….']),
                        KelasAktifSelect::make('kelas')
                            ->label('Kelas yang Diajar')
                            ->multiple()
                            ->relationship('kelas', 'nama_kelas', fn (Builder $query): Builder => $query->urutanTampilan())
                            ->columnSpanFull(),
                        Textarea::make('alamat')
                            ->label('Alamat')
                            ->required()
                            ->columnSpanFull(),
                    ]),
                AkunPortalSection::make(),
            ]);
    }
}
