<?php

namespace App\Filament\Resources\Kelas\Schemas;

use App\Enums\Tingkat;
use App\Filament\Forms\AturanFormRequest;
use App\Http\Requests\DataMaster\KelasRequest;
use App\Models\Kelas;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

/**
 * Form Kelas (PRD 6.2, 6.4). Aturan validasi: App\Http\Requests\DataMaster\KelasRequest.
 */
class KelasForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(AturanFormRequest::terapkan([
                TextInput::make('nama_kelas')->label('Nama Kelas'),
                Select::make('tingkat')->label('Tingkat')->options(Tingkat::class),
                TextInput::make('jurusan')->label('Jurusan'),
                Textarea::make('keterangan')->label('Keterangan')->columnSpanFull(),
            ], fn (?Kelas $record = null): array => KelasRequest::aturan($record)));
    }
}
