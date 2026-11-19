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

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Guru => 'Guru',
            self::Siswa => 'Siswa',
        };
    }

    /**
     * Nama menu/halaman beranda. Portal memakai "Beranda" yang lebih akrab bagi Guru/Siswa.
     */
    public function labelBeranda(): string
    {
        return $this === self::Admin ? 'Dashboard' : 'Beranda';
    }

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

    public function logoutRoute(): string
    {
        return $this === self::Admin ? 'filament.admin.auth.logout' : 'logout';
    }

    /**
     * Rute laporan yang dipakai peran ini (Admin: seluruh Kelas, portal: Kelas sendiri).
     */
    public function laporanRoute(): string
    {
        return $this === self::Admin ? 'laporan.show' : 'portal.laporan.show';
    }
}
