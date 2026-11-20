<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Data akun Admin baru (PRD 4.2: akun Admin dibuat secara administratif, tanpa registrasi publik).
 * Dipakai perintah `php artisan sekolah:buat-admin`.
 *
 * Cermin TypeScript: resources/js/types/contracts.ts → interface BuatAdminPayload.
 */
final class BuatAdminRequest extends FormRequest
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
        return self::aturan();
    }

    /**
     * @return array<string, list<mixed>>
     */
    public static function aturan(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return self::atribut();
    }

    /**
     * @return array<string, string>
     */
    public static function atribut(): array
    {
        return [
            'name' => 'Nama',
            'email' => 'Email',
            'password' => 'Password',
        ];
    }
}
