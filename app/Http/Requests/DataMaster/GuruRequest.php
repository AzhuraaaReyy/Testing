<?php

namespace App\Http\Requests\DataMaster;

use App\Enums\JenisKelamin;
use App\Models\Guru;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

/**
 * Aturan data Guru (PRD 7.2, 7.4). NIP opsional, unik bila diisi (termasuk data di Trash);
 * No. Telepon format Indonesia; minimal satu Kelas aktif. Dipakai form Filament lewat AturanFormRequest.
 *
 * Cermin TypeScript: resources/js/types/contracts.ts → interface GuruPayload.
 */
final class GuruRequest extends FormRequest
{
    /** 08xxxxxxxx atau +628xxxxxxxx, total 10–15 digit. */
    public const POLA_TELEPON = '/^(\+62|0)8[0-9]{7,12}$/';

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        $guru = $this->route('record');

        return self::aturan($guru instanceof Guru ? $guru : null);
    }

    /**
     * @param  Guru|null  $guru  Record yang sedang diedit (NIP miliknya tetap boleh dipakai).
     * @return array<string, list<mixed>>
     */
    public static function aturan(?Guru $guru = null): array
    {
        return [
            'nip' => ['nullable', 'string', 'max:30', Rule::unique('guru', 'nip')->ignore($guru?->getKey())],
            'nama' => ['required', 'string', 'max:150'],
            'jenis_kelamin' => ['required', Rule::enum(JenisKelamin::class)],
            'alamat' => ['required', 'string'],
            'no_telepon' => ['required', 'string', 'max:20', 'regex:'.self::POLA_TELEPON],
            'kelas' => ['required', 'array', 'min:1'],
            'kelas.*' => ['integer', SiswaRequest::kelasAktif()],
        ];
    }

    /**
     * Pemulihan dari Trash: wajib memilih minimal satu Kelas aktif (PRD 7.6).
     *
     * @return array<string, list<mixed>>
     */
    public static function aturanPemulihan(): array
    {
        return Arr::only(self::aturan(), ['kelas', 'kelas.*']);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return self::pesan();
    }

    /**
     * @return array<string, string>
     */
    public static function pesan(): array
    {
        return [
            'no_telepon.regex' => 'Format No. Telepon tidak valid. Gunakan 08… atau +628….',
        ];
    }
}
