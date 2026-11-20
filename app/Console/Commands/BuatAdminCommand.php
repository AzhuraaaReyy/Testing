<?php

namespace App\Console\Commands;

use App\Actions\Admin\BuatAdminAction;
use App\Http\Requests\Admin\BuatAdminRequest;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

/**
 * Membuat akun Admin untuk lingkungan production (PRD 4.2). Seeder hanya untuk pengembangan.
 *
 *   php artisan sekolah:buat-admin
 *   php artisan sekolah:buat-admin --nama="Admin Sekolah" --email=admin@sekolah.sch.id
 */
final class BuatAdminCommand extends Command
{
    protected $signature = 'sekolah:buat-admin
        {--nama= : Nama Admin}
        {--email= : Email login Admin}';

    protected $description = 'Membuat akun Admin baru (akun Admin dibuat secara administratif).';

    public function handle(BuatAdminAction $buatAdmin): int
    {
        // Password tidak pernah diterima lewat opsi agar tidak tersimpan di riwayat shell.
        $data = [
            'name' => $this->option('nama') ?? text('Nama Admin', required: true),
            'email' => $this->option('email') ?? text('Email', required: true),
            'password' => password('Password (min. 8 karakter, memuat huruf dan angka)', required: true),
            'password_confirmation' => password('Ulangi password', required: true),
        ];

        $validator = Validator::make($data, BuatAdminRequest::aturan(), attributes: BuatAdminRequest::atribut());

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $pesan) {
                $this->components->error($pesan);
            }

            return self::FAILURE;
        }

        $admin = $buatAdmin->handle($data['name'], $data['email'], $data['password']);

        $this->components->info("Akun Admin {$admin->email} berhasil dibuat. Login di ".route('filament.admin.auth.login'));

        return self::SUCCESS;
    }
}
