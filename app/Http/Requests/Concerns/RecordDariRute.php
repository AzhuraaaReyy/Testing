<?php

namespace App\Http\Requests\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Record yang sedang diubah lewat API (mis. PUT /api/v1/siswa/{siswa}), termasuk yang di Trash,
 * agar aturan unik mengecualikan nilai miliknya sendiri (PRD 13.3).
 *
 * @phpstan-require-extends FormRequest
 */
trait RecordDariRute
{
    /**
     * @template T of Model
     *
     * @param  class-string<T>  $kelas
     * @return T|null
     */
    protected function recordDariRute(string $kelas, string $parameter): ?Model
    {
        $id = $this->route($parameter);

        // Parameter rute API berupa string id (tanpa route model binding).
        if (! is_string($id)) {
            return null;
        }

        return $kelas::query()->withoutGlobalScope(SoftDeletingScope::class)->find($id);
    }
}
