<?php

namespace App\Http\Requests\DataMaster;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Aturan akun portal Guru/Siswa di form CRUD (PRD 22.2): email opsional (kosong = tanpa akun),
 * password wajib hanya saat akun baru dibuat; saat edit, kosong = password lama dipertahankan.
 *
 * Cermin TypeScript: resources/js/types/contracts.ts → interface AkunPortalPayload.
 */
final class AkunPortalRequest extends FormRequest
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
        return self::aturan(null, $this->string('email')->toString());
    }

    /**
     * @param  User|null  $akun  Akun yang sudah ada (null = akun baru).
     * @param  string|null  $email  Email yang diisi; menentukan apakah akun akan dibuat.
     * @return array<string, list<mixed>>
     */
    public static function aturan(?User $akun = null, ?string $email = null): array
    {
        return [
            'email' => ['nullable', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($akun?->getKey())],
            'password' => [filled($email) && $akun === null ? 'required' : 'nullable', 'string', 'min:8'],
        ];
    }

    /**
     * Aturan yang sama sebagai objek bersarang "akun" di payload Siswa/Guru (API).
     * Tanpa kunci "akun" = akun tidak diubah.
     *
     * @return array<string, list<mixed>>
     */
    public static function aturanBersarang(?User $akun = null, ?string $email = null): array
    {
        $aturan = ['akun' => ['sometimes', 'nullable', 'array:email,password']];

        foreach (self::aturan($akun, $email) as $field => $daftar) {
            $aturan["akun.{$field}"] = $daftar;
        }

        return $aturan;
    }
}
