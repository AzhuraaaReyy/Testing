<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Pilihan Jenis Kelamin untuk Siswa dan Guru (PRD 5.2, 7.2).
 *
 * Cermin TypeScript: resources/js/types/contracts.ts → JENIS_KELAMIN.
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
