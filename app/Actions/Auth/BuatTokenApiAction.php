<?php

namespace App\Actions\Auth;

use App\Models\User;

/**
 * Membuat Bearer token login API (Sanctum) dan membatasi token aktif per akun (SEC2-07):
 * token tertua di luar MAKS_TOKEN terbaru dicabut, sehingga login berulang (atau token yang
 * tercecer di banyak perangkat) tidak menumpuk tanpa batas.
 */
final class BuatTokenApiAction
{
    public const MAKS_TOKEN = 5;

    public const NAMA = 'api';

    /** @return string Token teks biasa ("{id}|{prefix}{acak}"), hanya ditampilkan sekali. */
    public function handle(User $user): string
    {
        $token = $user->createToken(self::NAMA)->plainTextToken;

        // id token ke-MAKS_TOKEN terbaru; semua token akun ini yang lebih lama dicabut.
        // (skip + value = OFFSET dengan LIMIT 1, valid di MySQL maupun SQLite.)
        $batas = $user->tokens()->orderByDesc('id')->skip(self::MAKS_TOKEN - 1)->value('id');

        if ($batas !== null) {
            $user->tokens()->where('id', '<', $batas)->delete();
        }

        return $token;
    }
}
