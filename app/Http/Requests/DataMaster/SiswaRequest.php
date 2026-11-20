<?php

namespace App\Http\Requests\DataMaster;

use App\Enums\JenisKelamin;
use App\Models\Siswa;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;

/**
 * Aturan data Siswa (PRD 5.2, 5.4, 5.6). NIS teks (nol di awal terjaga), unik termasuk terhadap
 * data di Trash (PRD 13.3); Kelas wajib satu Kelas aktif. Dipakai form Filament lewat AturanFormRequest.
 *
 * Cermin TypeScript: resources/js/types/contracts.ts → interface SiswaPayload.
 */
final class SiswaRequest extends FormRequest
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
        $siswa = $this->route('record');

        return self::aturan($siswa instanceof Siswa ? $siswa : null);
    }

    /**
     * @param  Siswa|null  $siswa  Record yang sedang diedit (NIS miliknya tetap boleh dipakai).
     * @return array<string, list<mixed>>
     */
    public static function aturan(?Siswa $siswa = null): array
    {
        return [
            'nis' => ['required', 'string', 'max:30', Rule::unique('siswa', 'nis')->ignore($siswa?->getKey())],
            'nama' => ['required', 'string', 'max:150'],
            'jenis_kelamin' => ['required', Rule::enum(JenisKelamin::class)],
            'alamat' => ['required', 'string'],
            'kelas_id' => ['required', 'integer', self::kelasAktif()],
        ];
    }

    /**
     * Pemulihan dari Trash: wajib memilih kembali satu Kelas aktif (PRD 5.7).
     *
     * @return array<string, list<mixed>>
     */
    public static function aturanPemulihan(): array
    {
        return Arr::only(self::aturan(), ['kelas_id']);
    }

    /**
     * Kelas di Trash tidak boleh dipilih. Dipakai juga oleh GuruRequest.
     */
    public static function kelasAktif(): Exists
    {
        return Rule::exists('kelas', 'id')->whereNull('deleted_at');
    }
}
