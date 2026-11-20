<?php

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\SekolahSeeder;

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

it('menolak berjalan di production meskipun dengan --force (SEC-04)', function (string $seeder) {
    $this->app['env'] = 'production';

    expect(fn () => $this->artisan('db:seed', ['--class' => $seeder, '--force' => true]))
        ->toThrow(RuntimeException::class, DatabaseSeeder::PESAN_PRODUKSI);

    expect(User::query()->count())->toBe(0)
        ->and(Kelas::query()->count())->toBe(0);
})->with([
    'DatabaseSeeder' => DatabaseSeeder::class,
    'SekolahSeeder' => SekolahSeeder::class,
]);

it('tetap berjalan di luar production', function () {
    $this->artisan('db:seed', ['--class' => DatabaseSeeder::class])->assertSuccessful();

    expect(User::query()->where('email', 'admin@sekolah.test')->exists())->toBeTrue();
});
