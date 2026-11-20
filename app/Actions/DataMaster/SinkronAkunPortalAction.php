<?php

namespace App\Actions\DataMaster;

use App\Models\Guru;
use App\Models\Siswa;

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
            $user?->delete();

            return;
        }

        // make() mengisi foreign key & role dari relasi (withAttributes).
        $user ??= $profil->akun()->make();
        $user->fill(['name' => $profil->nama, 'email' => $email]);

        if (filled($akun['password'] ?? null)) {
            $user->password = $akun['password'];
        }

        $user->save();
        $profil->setRelation('akun', $user);
    }
}
