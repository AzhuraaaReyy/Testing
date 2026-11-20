<?php

namespace App\Http\Resources;

use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Kelas yang tertaut pada Siswa/Guru (ringkas). Skema OpenAPI: components.schemas.KelasRingkas.
 *
 * @mixin Kelas
 */
final class KelasRingkasResource extends JsonResource
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
        ];
    }
}
