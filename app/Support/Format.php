<?php

namespace App\Support;

/**
 * Format tampilan sesuai PRD 18.
 */
final class Format
{
    /**
     * Pemisah ribuan titik: 1.000, 10.000. Nol tetap "0" (PRD 18.2).
     */
    public static function angka(int $nilai): string
    {
        return number_format($nilai, 0, ',', '.');
    }

    /**
     * Gabungan nama dipisah koma dalam satu rangkaian teks (PRD 8.3).
     *
     * @param  list<string>  $nama
     */
    public static function daftarNama(array $nama): string
    {
        return implode(', ', $nama);
    }
}
