<?php

namespace App\Http\Resources;

use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Data Kelas di API. Skema OpenAPI: docs/api/openapi.yaml → components.schemas.Kelas.
 *
 * @mixin Kelas
 */
final class KelasResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nama_kelas' => $this->nama_kelas,
            'tingkat' => $this->tingkat->value,
            'jurusan' => $this->jurusan,
            'keterangan' => $this->keterangan,
            // Hanya Siswa/Guru aktif yang dihitung (PRD 12.3).
            'jumlah_siswa' => $this->whenCounted('siswa'),
            'jumlah_guru' => $this->whenCounted('guru'),
            'dihapus_pada' => $this->deleted_at?->toIso8601String(),
        ];
    }
}
