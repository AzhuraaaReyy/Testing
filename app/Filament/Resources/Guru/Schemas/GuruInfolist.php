<?php

namespace App\Filament\Resources\Guru\Schemas;

use App\Filament\LabelBadge;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

/**
 * Detail Guru, read-only pada halaman terpisah (PRD 7.5).
 */
class GuruInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('nip')->label('NIP')->placeholder('-'),
                TextEntry::make('nama')->label('Nama Guru'),
                TextEntry::make('jenis_kelamin')->label('Jenis Kelamin'),
                TextEntry::make('no_telepon')->label('No. Telepon'),
                TextEntry::make('kelas.nama_kelas')->label('Kelas yang Diajar')->formatStateUsing(LabelBadge::format(...))->html()->placeholder('-')->columnSpanFull(),
                TextEntry::make('alamat')->label('Alamat')->columnSpanFull(),
                TextEntry::make('akun.email')->label('Email Akun Portal')->placeholder('Belum memiliki akun'),
            ]);
    }
}
