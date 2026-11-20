<?php

namespace App\Http\Requests\DataMaster;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Pemulihan Siswa dari Trash: wajib memilih kembali satu Kelas aktif (PRD 5.7).
 * Aturan = SiswaRequest::aturanPemulihan() (sama dengan dialog Pulihkan di panel Admin).
 *
 * Cermin TypeScript: resources/js/types/contracts.ts → interface PulihkanSiswaPayload.
 */
final class PulihkanSiswaRequest extends FormRequest
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
        return SiswaRequest::aturanPemulihan();
    }

    public function kelasId(): int
    {
        return (int) $this->validated('kelas_id');
    }
}
