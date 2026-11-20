<?php

use App\Enums\Tingkat;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Siswa;

/*
| Aturan integritas relasi Soft Delete (PRD 12.4, 21) dijaga di model,
| sehingga berlaku dari Filament, seeder, maupun kode lain.
*/

it('melepas Kelas saat Siswa masuk Trash dan mewajibkan Kelas saat dipulihkan', function () {
    $siswa = Siswa::factory()->create();
    $kelasBaru = Kelas::factory()->create();

    $siswa->delete();

    expect($siswa->fresh())->trashed()->toBeTrue()->kelas_id->toBeNull();

    expect($siswa->pulihkanKeKelas($kelasBaru->id))->toBeTrue()
        ->and($siswa->fresh())->trashed()->toBeFalse()->kelas_id->toBe($kelasBaru->id);
});

it('melepas seluruh relasi guru_kelas saat Guru masuk Trash dan menyambung ulang saat dipulihkan', function () {
    $guru = Guru::factory()->hasAttached(Kelas::factory()->count(2))->create();
    $kelas = Kelas::factory()->count(2)->create();

    $guru->delete();

    expect($guru->kelas()->count())->toBe(0);

    $guru->pulihkanKeKelas($kelas->modelKeys());

    expect($guru->fresh())->trashed()->toBeFalse()
        ->and($guru->kelas()->pluck('kelas.id')->sort()->values()->all())->toBe($kelas->modelKeys());
});

it('menolak menghapus Kelas yang masih memiliki Siswa atau Guru aktif', function (Closure $isi, string $alasan) {
    $kelas = Kelas::factory()->create();
    $isi($kelas);

    expect($kelas->alasanTidakDapatDihapus())->toBe($alasan)
        ->and($kelas->delete())->toBeFalse()
        ->and($kelas->fresh()->trashed())->toBeFalse();
})->with([
    'Siswa aktif' => [fn (Kelas $kelas) => Siswa::factory()->for($kelas)->create(), 'Kelas masih memiliki Siswa aktif.'],
    'Guru aktif' => [fn (Kelas $kelas) => Guru::factory()->hasAttached($kelas)->create(), 'Kelas masih memiliki Guru aktif yang terhubung.'],
]);

it('mengizinkan menghapus Kelas yang relasinya hanya dengan data di Trash', function () {
    $kelas = Kelas::factory()->create();
    Siswa::factory()->for($kelas)->create()->delete();
    Guru::factory()->hasAttached($kelas)->create()->delete();

    expect($kelas->delete())->toBeTrue();
});

it('mengurutkan Kelas yang diajar Guru: Tingkat, Jurusan, lalu Nama Kelas', function () {
    $guru = Guru::factory()->create();
    $guru->kelas()->attach([
        Kelas::factory()->create(['tingkat' => Tingkat::XII, 'jurusan' => 'IPA', 'nama_kelas' => 'XII IPA 1'])->id,
        Kelas::factory()->create(['tingkat' => Tingkat::X, 'jurusan' => 'IPS', 'nama_kelas' => 'X IPS 1'])->id,
        Kelas::factory()->create(['tingkat' => Tingkat::X, 'jurusan' => 'IPA', 'nama_kelas' => 'X IPA 2'])->id,
        Kelas::factory()->create(['tingkat' => Tingkat::XI, 'jurusan' => 'IPA', 'nama_kelas' => 'XI IPA 1'])->id,
    ]);

    expect($guru->kelas->pluck('nama_kelas')->all())->toBe(['X IPA 2', 'X IPS 1', 'XI IPA 1', 'XII IPA 1']);
});
