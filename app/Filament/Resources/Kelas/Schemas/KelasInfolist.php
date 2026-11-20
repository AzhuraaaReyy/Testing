<?php

namespace App\Filament\Resources\Kelas\Schemas;

use App\Filament\LabelBadge;
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
                TextEntry::make('tingkat')->label('Tingkat')->formatStateUsing(LabelBadge::format(...))->html(),
                TextEntry::make('jurusan')->label('Jurusan')->placeholder('-'),
                TextEntry::make('keterangan')->label('Keterangan')->placeholder('-')->columnSpanFull(),
            ]);
    }
}
