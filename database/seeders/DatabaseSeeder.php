<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Sengaja TANPA WithoutModelEvents: aturan integritas Trash (melepas relasi Kelas)
 * dijalankan oleh event model dan harus berlaku juga saat seeding.
 */
class DatabaseSeeder extends Seeder
{
    use HanyaPengembangan;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->tolakDiProduksi();

        // Akun Admin dibuat secara administratif (PRD 4.2). Kredensial ini hanya untuk pengembangan lokal.
        User::factory()->create([
            'name' => 'Admin Sekolah',
            'email' => 'admin@sekolah.test',
        ]);

        $this->call(SekolahSeeder::class);
    }
}
