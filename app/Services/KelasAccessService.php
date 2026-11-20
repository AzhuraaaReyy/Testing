<?php

namespace App\Services;

use App\Enums\Role;
use App\Models\User;

/**
 * Menentukan Kelas mana yang boleh dilihat seorang pengguna pada laporan.
 * Admin: seluruh Kelas. Siswa: Kelasnya sendiri. Guru: Kelas yang diajar (PRD v1.3).
 */
final class KelasAccessService
{
    /**
     * @return list<int>|null null = tanpa batasan (seluruh Kelas aktif).
     */
    public function kelasIdsFor(User $user): ?array
    {
        return match ($user->role) {
            Role::Admin => null,
            Role::Siswa => $user->siswa?->kelas_id !== null ? [$user->siswa->kelas_id] : [],
            Role::Guru => array_values($user->guru?->kelas()->pluck('kelas.id')->map(fn (mixed $id): int => (int) $id)->all() ?? []),
        };
    }
}
