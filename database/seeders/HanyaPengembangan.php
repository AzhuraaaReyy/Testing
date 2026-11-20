<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\App;
use RuntimeException;

/**
 * Seeder data contoh membuat akun dengan password publik ("password"), sehingga tidak boleh
 * berjalan di production, bahkan dengan --force (SEC-04). Akun Admin production dibuat lewat
 * perintah `php artisan sekolah:buat-admin`.
 */
trait HanyaPengembangan
{
    public const PESAN_PRODUKSI = 'Seeder data contoh tidak boleh dijalankan di production karena membuat akun '
        .'dengan password default. Buat akun Admin dengan: php artisan sekolah:buat-admin';

    protected function tolakDiProduksi(): void
    {
        if (App::isProduction()) {
            throw new RuntimeException(self::PESAN_PRODUKSI);
        }
    }
}
