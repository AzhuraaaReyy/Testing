<?php

namespace App\Actions\DataMaster;

use App\Models\Guru;
use App\Models\Siswa;
use App\Support\LogKeamanan;
use Illuminate\Support\Facades\Auth;

/**
 * Akun login portal milik profil Siswa/Guru (PRD 22.2). Dipakai form Filament dan API.
 * - Email kosong = tanpa akun; akun yang sudah ada dihapus.
 * - Password kosong saat edit = password lama dipertahankan (validasi: AkunPortalRequest).
 * - Nama akun selalu mengikuti nama profil; peran dipaksa oleh relasi akun(), bukan input.
 */
final class SinkronAkunPortalAction
{
    /**
     * @param  array{email?: string|null, password?: string|null}  $akun
     */
    public function handle(Siswa|Guru $profil, array $akun): void
    {
        $email = $akun['email'] ?? null;
        $user = $profil->akun;

        if (blank($email)) {
            if ($user !== null) {
                // Token API tidak punya foreign key ke users; cabut agar tidak tersisa token yatim.
                $user->tokens()->delete();
                $user->delete();
                LogKeamanan::catat('akun.dihapus', ['user_id' => $user->id, 'oleh' => Auth::id()], 'notice');
            }

            return;
        }

        // make() mengisi foreign key & role dari relasi (withAttributes).
        $user ??= $profil->akun()->make();
        $user->fill(['name' => $profil->nama, 'email' => $email]);

        // Password akun yang sudah ada diganti (mis. akun diambil alih): semua token API dicabut.
        // Session portal lain berakhir lewat middleware auth.session (SEC2-01).
        $gantiPassword = $user->exists && filled($akun['password'] ?? null);

        if (filled($akun['password'] ?? null)) {
            $user->password = $akun['password'];
        }

        $user->save();

        if ($gantiPassword) {
            $user->tokens()->delete();
            LogKeamanan::catat('akun.password_diganti', ['user_id' => $user->id, 'oleh' => Auth::id()], 'notice');
        }
        $profil->setRelation('akun', $user);
    }
}
