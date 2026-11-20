<?php

use App\Models\Guru;
use App\Models\Siswa;
use Database\Seeders\DatabaseSeeder;

/*
| Seeder harus menghasilkan data yang mematuhi aturan integritas (PRD 12.4, 21):
| data di Trash tidak lagi terhubung ke Kelas.
*/
it('menyemai data Trash tanpa relasi Kelas', function () {
    $this->seed(DatabaseSeeder::class);

    $siswaTrash = Siswa::onlyTrashed()->get();
    $guruTrash = Guru::onlyTrashed()->get();

    expect($siswaTrash)->not->toBeEmpty()
        ->and($siswaTrash->pluck('kelas_id')->filter()->all())->toBe([])
        ->and($guruTrash)->not->toBeEmpty()
        ->and($guruTrash->sum(fn (Guru $guru): int => $guru->kelas()->count()))->toBe(0);
});
