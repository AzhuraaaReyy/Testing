<?php

namespace App\Filament\Resources\Kelas\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

/**
 * Detail Kelas, read-only (PRD 6.5).
 */
class KelasInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('nama_kelas')->label('Nama Kelas'),
                TextEntry::make('tingkat')->label('Tingkat')->badge(),
                TextEntry::make('jurusan')->label('Jurusan')->placeholder('-'),
                TextEntry::make('keterangan')->label('Keterangan')->placeholder('-')->columnSpanFull(),
            ]);
    }
}
