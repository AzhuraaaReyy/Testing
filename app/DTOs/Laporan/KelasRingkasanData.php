<?php

namespace App\DTOs\Laporan;

use App\Enums\Tingkat;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Database\Eloquent\Collection;

/**
 * Kontrak data satu baris laporan (satu Kelas aktif).
 * View hanya bergantung pada bentuk ini, bukan pada model Eloquent.
 *
 * Cermin TypeScript: resources/js/types/contracts.ts → interface KelasRingkasanData
 * (dijaga sinkron oleh tests/Unit/ContractSyncTest.php).
 */
final readonly class KelasRingkasanData
{
    /**
     * @param  list<string>  $siswa  Nama Siswa aktif, terurut A–Z.
     * @param  list<string>  $guru  Nama Guru aktif, terurut A–Z.
     */
    public function __construct(
        public int $id,
        public string $namaKelas,
        public Tingkat $tingkat,
        public ?string $jurusan,
        public array $siswa = [],
        public array $guru = [],
    ) {}

    public static function fromModel(Kelas $kelas): self
    {
        return new self(
            id: $kelas->id,
            namaKelas: $kelas->nama_kelas,
            tingkat: $kelas->tingkat,
            jurusan: $kelas->jurusan,
            siswa: $kelas->relationLoaded('siswa') ? self::namaTerurut($kelas->siswa) : [],
            guru: $kelas->relationLoaded('guru') ? self::namaTerurut($kelas->guru) : [],
        );
    }

    /**
     * Diurutkan di PHP (bukan SQL) agar hasilnya sama di semua driver/collation.
     *
     * @param  Collection<int, Siswa|Guru>  $models
     * @return list<string>
     */
    private static function namaTerurut(Collection $models): array
    {
        return $models
            ->pluck('nama')
            ->sort(fn (string $a, string $b): int => strnatcasecmp($a, $b))
            ->values()
            ->all();
    }
}
