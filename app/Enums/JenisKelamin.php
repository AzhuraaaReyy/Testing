<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Pilihan Jenis Kelamin untuk Siswa dan Guru (PRD 5.2, 7.2).
 */
enum JenisKelamin: string implements HasLabel
{
    case LakiLaki = 'L';
    case Perempuan = 'P';

    public function getLabel(): string
    {
        return match ($this) {
            self::LakiLaki => 'Laki-laki',
            self::Perempuan => 'Perempuan',
        };
    }
}
