<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Akun Admin dibuat secara administratif (PRD 4.2). Kredensial ini hanya untuk pengembangan lokal.
        User::factory()->create([
            'name' => 'Admin Sekolah',
            'email' => 'admin@sekolah.test',
        ]);

        $this->call(SekolahSeeder::class);
    }
}
