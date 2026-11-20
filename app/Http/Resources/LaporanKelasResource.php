<?php

namespace App\Http\Resources;

use App\DTOs\Laporan\KelasRingkasanData;
use App\Enums\JenisLaporan;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Satu baris laporan = satu Kelas aktif (PRD 8–10). Kolom daftar nama mengikuti jenis laporan
 * pada rute ({jenis}). Skema OpenAPI: components.schemas.LaporanKelas.
 *
 * @property KelasRingkasanData $resource
 */
final class LaporanKelasResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $jenis = $request->route('jenis');

        return [
            'id' => $this->resource->id,
            'nama_kelas' => $this->resource->namaKelas,
            'tingkat' => $this->resource->tingkat->value,
            'jurusan' => $this->resource->jurusan,
            // Nama A–Z; daftar kosong = "Belum ada data".
            'siswa' => $this->when($jenis instanceof JenisLaporan && $jenis->menampilkanSiswa(), $this->resource->siswa),
            'guru' => $this->when($jenis instanceof JenisLaporan && $jenis->menampilkanGuru(), $this->resource->guru),
        ];
    }
}
