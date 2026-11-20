<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use LogicException;

/**
 * Tingkat Kelas (PRD 6.2). Urutan case = urutan tampilan X → XI → XII.
 */
enum Tingkat: string implements HasLabel
{
    case X = 'X';
    case XI = 'XI';
    case XII = 'XII';

    public function getLabel(): string
    {
        return $this->value;
    }

    /**
     * Posisi urut (0-based) untuk pengurutan Tingkat X, XI, XII.
     */
    public function urutan(): int
    {
        foreach (self::cases() as $posisi => $tingkat) {
            if ($tingkat === $this) {
                return $posisi;
            }
        }

        throw new LogicException("Tingkat [{$this->value}] tidak ada di daftar case.");
    }

    /**
     * Binding untuk CASE pengurutan di SQL (lihat scope Kelas::urutanTampilan): pasangan
     * nilai => posisi per Tingkat, lalu posisi untuk nilai tak dikenal (ELSE).
     *
     * @return list<string|int>
     */
    public static function bindingUrutan(): array
    {
        $bindings = [];

        foreach (self::cases() as $tingkat) {
            $bindings[] = $tingkat->value;
            $bindings[] = $tingkat->urutan();
        }

        $bindings[] = count(self::cases());

        return $bindings;
    }
}
