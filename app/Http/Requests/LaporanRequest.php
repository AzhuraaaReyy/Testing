<?php

namespace App\Http\Requests;

use App\Http\PartialResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Skema query string halaman laporan. Laporan tidak memiliki pencarian/filter (PRD 2.4),
 * sehingga satu-satunya parameter yang diterima adalah `page`.
 *
 * Cermin TypeScript: resources/js/types/contracts.ts → LaporanQuery.
 */
final class LaporanRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Akses dibatasi middleware `auth`; seluruh Admin memiliki hak yang sama (PRD 1.3).
        return true;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1'],
        ];
    }

    public function halaman(): int
    {
        return (int) $this->validated('page', 1);
    }

    public function memintaFragmenTabel(): bool
    {
        return PartialResponse::requested($this, PartialResponse::TABEL);
    }

    /**
     * Navigasi biasa: `page` tidak valid (mis. "abc" atau 0) diarahkan ke halaman pertama.
     * Permintaan parsial & API (JSON): biarkan Laravel membalas 422 JSON standar.
     */
    protected function failedValidation(Validator $validator): void
    {
        if (PartialResponse::isPartial($this) || $this->expectsJson()) {
            parent::failedValidation($validator);
        }

        throw new HttpResponseException(redirect()->to($this->url()));
    }
}
