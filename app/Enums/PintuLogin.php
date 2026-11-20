<?php

namespace App\Enums;

/**
 * Tiga pintu login sistem. Aturan login sama (PRD 4.4–4.5), tetapi penghitung percobaan gagal
 * dipisah per pintu: mencoba akun di pintu yang salah tidak ikut mengunci pintu yang benar.
 *
 * Cermin TypeScript: resources/js/types/contracts.ts → PINTU_LOGIN.
 */
enum PintuLogin: string
{
    /** Portal Guru/Siswa (Blade, POST /login). */
    case Portal = 'portal';

    /** Panel Admin (Filament, /admin/login). */
    case Admin = 'admin';

    /** API (POST /api/v1/login); semua peran. */
    case Api = 'api';

    /**
     * Peran yang boleh login melalui pintu ini.
     *
     * @return list<Role>
     */
    public function peran(): array
    {
        return match ($this) {
            self::Portal => [Role::Guru, Role::Siswa],
            self::Admin => [Role::Admin],
            self::Api => Role::cases(),
        };
    }
}
