<?php

namespace App\Enums;

/**
 * Pilihan Jenis Kelamin untuk Siswa dan Guru (PRD 5.2, 7.2).
 */
enum JenisKelamin: string
{
    case LakiLaki = 'L';
    case Perempuan = 'P';

    public function label(): string
    {
        return match ($this) {
            self::LakiLaki => 'Laki-laki',
            self::Perempuan => 'Perempuan',
        };
    }
}
