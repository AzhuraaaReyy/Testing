<?php

namespace App\Enums;

/**
 * Tiga halaman laporan (PRD Fitur 5–7, portal: Bagian 22). Nilai enum = segmen URL `/laporan/{jenis}`.
 * Satu enum mendefinisikan seluruh perbedaan antar-laporan, sehingga controller, action,
 * dan view cukup satu set yang dapat dipakai ulang.
 *
 * Penamaan mengikuti sudut pandang peran: Admin melihat seluruh Kelas, sedangkan Guru/Siswa
 * hanya Kelasnya sendiri sehingga memakai nama yang lebih personal ("Teman Sekelas", dst).
 * Nama menu dan judul halaman sengaja sama agar pengguna tahu sedang berada di mana.
 *
 * Cermin TypeScript: resources/js/types/contracts.ts → type JenisLaporan.
 */
enum JenisLaporan: string
{
    case SiswaPerKelas = 'siswa-per-kelas';
    case GuruPerKelas = 'guru-per-kelas';
    case Gabungan = 'siswa-kelas-guru';

    public function judul(Role $role = Role::Admin): string
    {
        return match ($role) {
            Role::Admin => match ($this) {
                self::SiswaPerKelas => 'Daftar Siswa per Kelas',
                self::GuruPerKelas => 'Daftar Guru per Kelas',
                self::Gabungan => 'Daftar Siswa, Kelas, dan Guru',
            },
            Role::Siswa => match ($this) {
                self::SiswaPerKelas => 'Teman Sekelas',
                self::GuruPerKelas => 'Guru Pengajar',
                self::Gabungan => 'Ringkasan Kelas',
            },
            Role::Guru => match ($this) {
                self::SiswaPerKelas => 'Siswa Saya',
                self::GuruPerKelas => 'Rekan Pengajar',
                self::Gabungan => 'Ringkasan Kelas',
            },
        };
    }

    public function deskripsi(Role $role = Role::Admin): string
    {
        return match ($role) {
            Role::Admin => match ($this) {
                self::SiswaPerKelas => 'Siswa aktif dikelompokkan berdasarkan Kelas.',
                self::GuruPerKelas => 'Guru aktif dikelompokkan berdasarkan Kelas yang diajar.',
                self::Gabungan => 'Siswa dan Guru aktif pada setiap Kelas dalam satu tabel.',
            },
            Role::Siswa => match ($this) {
                self::SiswaPerKelas => 'Daftar Siswa di Kelas Anda.',
                self::GuruPerKelas => 'Daftar Guru yang mengajar di Kelas Anda.',
                self::Gabungan => 'Siswa dan Guru di Kelas Anda dalam satu tabel.',
            },
            Role::Guru => match ($this) {
                self::SiswaPerKelas => 'Daftar Siswa pada setiap Kelas yang Anda ajar.',
                self::GuruPerKelas => 'Daftar Guru yang mengajar di Kelas yang sama dengan Anda.',
                self::Gabungan => 'Siswa dan Guru pada setiap Kelas yang Anda ajar dalam satu tabel.',
            },
        };
    }

    public function judulTabel(): string
    {
        return match ($this) {
            self::SiswaPerKelas => 'Siswa berdasarkan Kelas',
            self::GuruPerKelas => 'Guru berdasarkan Kelas',
            self::Gabungan => 'Siswa dan Guru berdasarkan Kelas',
        };
    }

    /**
     * Label menu: Admin memakai versi ringkas; portal memakai judul halaman yang sama persis.
     */
    public function labelMenu(Role $role = Role::Admin): string
    {
        if ($role !== Role::Admin) {
            return $this->judul($role);
        }

        return match ($this) {
            self::SiswaPerKelas => 'Siswa per Kelas',
            self::GuruPerKelas => 'Guru per Kelas',
            self::Gabungan => 'Siswa, Kelas & Guru',
        };
    }

    /**
     * Label kolom Kelas mengikuti struktur tabel PRD 8.2, 9.2, dan 10.2.
     */
    public function labelKolomKelas(): string
    {
        return $this === self::Gabungan ? 'Kelas' : 'Nama Kelas';
    }

    public function menampilkanSiswa(): bool
    {
        return $this !== self::GuruPerKelas;
    }

    public function menampilkanGuru(): bool
    {
        return $this !== self::SiswaPerKelas;
    }

    /**
     * Jumlah kolom tabel: No., Kelas, Tingkat, Jurusan, ditambah kolom daftar yang ditampilkan.
     */
    public function jumlahKolom(): int
    {
        return 4 + (int) $this->menampilkanSiswa() + (int) $this->menampilkanGuru();
    }

    public function icon(): Icon
    {
        return match ($this) {
            self::SiswaPerKelas => Icon::ClipboardList,
            self::GuruPerKelas => Icon::SquareUser,
            self::Gabungan => Icon::Table,
        };
    }
}
