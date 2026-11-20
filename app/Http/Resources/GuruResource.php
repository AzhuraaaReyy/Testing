<?php

namespace App\Http\Resources;

use App\Models\Guru;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Data Guru di API. Skema OpenAPI: docs/api/openapi.yaml → components.schemas.Guru.
 *
 * @mixin Guru
 */
final class GuruResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nip' => $this->nip,
            'nama' => $this->nama,
            'jenis_kelamin' => $this->jenis_kelamin->value,
            'alamat' => $this->alamat,
            'no_telepon' => $this->no_telepon,
            // Terurut Tingkat → Jurusan → Nama Kelas; kosong untuk Guru di Trash (PRD 7.6).
            'kelas' => KelasRingkasResource::collection($this->whenLoaded('kelas')),
            'akun_email' => $this->whenLoaded('akun', fn (): ?string => $this->akun?->email),
            'dihapus_pada' => $this->deleted_at?->toIso8601String(),
        ];
    }
}
