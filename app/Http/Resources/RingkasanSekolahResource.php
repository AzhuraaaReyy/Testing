<?php

namespace App\Http\Resources;

use App\DTOs\Dashboard\RingkasanSekolahData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Total Siswa, Kelas, dan Guru aktif (PRD 11.3, 22.3). Skema OpenAPI: components.schemas.Ringkasan.
 *
 * @property RingkasanSekolahData $resource
 */
final class RingkasanSekolahResource extends JsonResource
{
    /**
     * @return array<string, int>
     */
    public function toArray(Request $request): array
    {
        return [
            'total_siswa' => $this->resource->totalSiswa,
            'total_kelas' => $this->resource->totalKelas,
            'total_guru' => $this->resource->totalGuru,
        ];
    }
}
