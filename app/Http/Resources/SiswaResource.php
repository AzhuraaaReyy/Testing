<?php

namespace App\Http\Resources;

use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Data Siswa di API. Skema OpenAPI: docs/api/openapi.yaml → components.schemas.Siswa.
 *
 * @mixin Siswa
 */
final class SiswaResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nis' => $this->nis,
            'nama' => $this->nama,
            'jenis_kelamin' => $this->jenis_kelamin->value,
            'alamat' => $this->alamat,
            // null untuk Siswa di Trash (relasi Kelas dilepas, PRD 5.7).
            'kelas' => KelasRingkasResource::make($this->whenLoaded('kelas')),
            'akun_email' => $this->whenLoaded('akun', fn (): ?string => $this->akun?->email),
            'dihapus_pada' => $this->deleted_at?->toIso8601String(),
        ];
    }
}
