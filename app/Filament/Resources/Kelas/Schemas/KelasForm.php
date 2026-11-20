<?php

namespace App\Filament\Resources\Kelas\Schemas;

use App\Enums\Tingkat;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

/**
 * Form Kelas (PRD 6.2, 6.4). Nama Kelas unik termasuk terhadap data di Trash (PRD 13.3).
 */
class KelasForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama_kelas')
                    ->label('Nama Kelas')
                    ->required()
                    ->maxLength(100)
                    ->unique(ignoreRecord: true),
                Select::make('tingkat')
                    ->label('Tingkat')
                    ->options(Tingkat::class)
                    ->required(),
                TextInput::make('jurusan')
                    ->label('Jurusan')
                    ->maxLength(100),
                Textarea::make('keterangan')
                    ->label('Keterangan')
                    ->columnSpanFull(),
            ]);
    }
}
