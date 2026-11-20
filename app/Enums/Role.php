<?php

namespace App\Enums;

/**
 * Peran pengguna (PRD v1.3). Admin mengelola data lewat panel Filament;
 * Guru dan Siswa memakai portal Blade yang bersifat read-only.
 *
 * Cermin TypeScript: resources/js/types/contracts.ts → type Role.
 */
enum Role: string
{
    case Admin = 'admin';
    case Guru = 'guru';
    case Siswa = 'siswa';

    /**
     * Nama grup menu laporan, dari sudut pandang pengguna.
     */
    public function labelGrupLaporan(): string
    {
        return match ($this) {
            self::Admin => 'Laporan',
            self::Guru => 'Kelas yang Saya Ajar',
            self::Siswa => 'Kelas Saya',
        };
    }

    public function homeRoute(): string
    {
        return $this === self::Admin ? 'filament.admin.pages.dashboard' : 'portal.dashboard';
    }

    public function loginRoute(): string
    {
        return $this === self::Admin ? 'filament.admin.auth.login' : 'login';
    }

    /**
     * Awalan path halaman yang boleh dibuka peran ini (tujuan setelah login).
     */
    public function wilayahUrl(): string
    {
        return $this === self::Admin ? 'admin' : 'portal';
    }

    /**
     * Penjelasan saat akun yang sedang login membuka halaman login pintu lain. Satu browser
     * memegang satu akun (session sama), jadi pengguna perlu logout dulu.
     */
    public function pesanSudahLogin(): string
    {
        return $this === self::Admin
            ? 'Anda sedang login sebagai Admin. Logout terlebih dahulu untuk masuk sebagai Guru atau Siswa.'
            : 'Anda sedang login di portal Guru/Siswa. Logout terlebih dahulu untuk masuk sebagai Admin.';
    }
}
