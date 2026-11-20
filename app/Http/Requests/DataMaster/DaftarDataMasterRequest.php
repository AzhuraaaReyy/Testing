<?php

namespace App\Http\Requests\DataMaster;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Query string daftar Siswa/Kelas/Guru di API (GET /api/v1/{siswa|kelas|guru}).
 * Tanpa pencarian/filter (PRD 2.4); "trash" hanya memilih tab Aktif/Trash (PRD 13.2).
 *
 * Cermin TypeScript: resources/js/types/contracts.ts → interface DaftarDataMasterQuery.
 */
final class DaftarDataMasterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            'trash' => ['sometimes', 'boolean'],
        ];
    }

    public function halaman(): int
    {
        return (int) $this->validated('page', 1);
    }

    public function trash(): bool
    {
        return $this->boolean('trash');
    }
}
