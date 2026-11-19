<?php

namespace App\DTOs\Dashboard;

/**
 * Ringkasan jumlah data aktif sekolah untuk kartu Dashboard (PRD 11.3).
 *
 * Cermin TypeScript: resources/js/types/contracts.ts → interface RingkasanSekolahData.
 */
final readonly class RingkasanSekolahData
{
    public function __construct(
        public int $totalSiswa,
        public int $totalKelas,
        public int $totalGuru,
    ) {}
}
