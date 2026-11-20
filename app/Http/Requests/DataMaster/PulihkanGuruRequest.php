<?php

namespace App\Http\Requests\DataMaster;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Pemulihan Guru dari Trash: wajib memilih minimal satu Kelas aktif (PRD 7.6).
 * Aturan = GuruRequest::aturanPemulihan() (sama dengan dialog Pulihkan di panel Admin).
 *
 * Cermin TypeScript: resources/js/types/contracts.ts → interface PulihkanGuruPayload.
 */
final class PulihkanGuruRequest extends FormRequest
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
        return GuruRequest::aturanPemulihan();
    }

    /**
     * @return list<int>
     */
    public function kelasIds(): array
    {
        $kelas = $this->validated('kelas');

        return is_array($kelas) ? array_values(array_map(intval(...), $kelas)) : [];
    }
}
