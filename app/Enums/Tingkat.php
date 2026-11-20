<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

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
        return array_search($this, self::cases(), true);
    }

    /**
     * Ekspresi SQL portabel (MySQL/SQLite) untuk mengurutkan kolom tingkat sesuai urutan enum,
     * bukan urutan alfabet ("XI" < "XII" < "X" bila diurutkan sebagai teks).
     */
    public static function orderBySql(string $column = 'tingkat'): string
    {
        $branches = collect(self::cases())
            ->map(fn (self $tingkat): string => "WHEN '{$tingkat->value}' THEN {$tingkat->urutan()}")
            ->implode(' ');

        return "CASE {$column} {$branches} ELSE ".count(self::cases()).' END';
    }
}
