<?php

namespace App\Filament\Resources\Siswa\Schemas;

use App\Filament\LabelBadge;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

/**
 * Detail Siswa, read-only pada halaman terpisah (PRD 5.5).
 */
class SiswaInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('nis')->label('NIS'),
                TextEntry::make('nama')->label('Nama Siswa'),
                TextEntry::make('jenis_kelamin')->label('Jenis Kelamin'),
                TextEntry::make('kelas.nama_kelas')->label('Kelas')->formatStateUsing(LabelBadge::format(...))->html()->placeholder('-'),
                TextEntry::make('alamat')->label('Alamat')->columnSpanFull(),
                TextEntry::make('akun.email')->label('Email Akun Portal')->placeholder('Belum memiliki akun'),
            ]);
    }
}
