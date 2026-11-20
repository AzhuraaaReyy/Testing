<?php

namespace App\Http\Requests\DataMaster;

use App\Enums\Tingkat;
use App\Models\Kelas;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Aturan data Kelas (PRD 6.2, 6.4). Nama Kelas unik termasuk terhadap data di Trash (PRD 13.3):
 * index unik biasa, tanpa pengecualian deleted_at. Dipakai form Filament lewat AturanFormRequest.
 *
 * Cermin TypeScript: resources/js/types/contracts.ts → interface KelasPayload.
 */
final class KelasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        $kelas = $this->route('record');

        return self::aturan($kelas instanceof Kelas ? $kelas : null);
    }

    /**
     * @param  Kelas|null  $kelas  Record yang sedang diedit (Nama Kelas miliknya tetap boleh dipakai).
     * @return array<string, list<mixed>>
     */
    public static function aturan(?Kelas $kelas = null): array
    {
        return [
            'nama_kelas' => ['required', 'string', 'max:100', Rule::unique('kelas', 'nama_kelas')->ignore($kelas?->getKey())],
            'tingkat' => ['required', Rule::enum(Tingkat::class)],
            'jurusan' => ['nullable', 'string', 'max:100'],
            'keterangan' => ['nullable', 'string'],
        ];
    }
}
