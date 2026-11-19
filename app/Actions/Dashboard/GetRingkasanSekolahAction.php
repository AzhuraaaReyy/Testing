<?php

namespace App\Actions\Dashboard;

use App\DTOs\Dashboard\RingkasanSekolahData;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Siswa;

/**
 * Total Siswa, Kelas, dan Guru aktif (data di Trash tidak dihitung, PRD 12.3).
 * Dipakai Dashboard portal; dapat dipakai ulang oleh widget Dashboard Filament.
 */
final class GetRingkasanSekolahAction
{
    public function handle(): RingkasanSekolahData
    {
        return new RingkasanSekolahData(
            totalSiswa: Siswa::query()->whereNotNull('kelas_id')->count(),
            totalKelas: Kelas::query()->count(),
            totalGuru: Guru::query()->count(),
        );
    }
}
