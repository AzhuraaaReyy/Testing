<?php

use App\Enums\Tingkat;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

/*
| API laporan & ringkasan: sama dengan web (PRD 8–11, 22). Setiap Kelas satu kali, 10 per halaman.
*/

it('menyajikan laporan gabungan: satu baris per Kelas, nama A–Z, tanpa data Trash', function () {
    Sanctum::actingAs(User::factory()->create());
    $kelas = Kelas::factory()->create(['nama_kelas' => 'X IPA 1', 'tingkat' => Tingkat::X]);
    Siswa::factory()->for($kelas)->create(['nama' => 'Citra']);
    Siswa::factory()->for($kelas)->create(['nama' => 'agus']);
    Siswa::factory()->for($kelas)->create(['nama' => 'Terhapus'])->delete();
    Guru::factory()->count(2)->sequence(['nama' => 'Bu Sari'], ['nama' => 'Pak Andi'])->hasAttached($kelas)->create();

    $this->getJson('/api/v1/laporan/siswa-kelas-guru')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('jenis', 'siswa-kelas-guru')
        ->assertJsonPath('data.0.nama_kelas', 'X IPA 1')
        ->assertJsonPath('data.0.siswa', ['agus', 'Citra'])
        ->assertJsonPath('data.0.guru', ['Bu Sari', 'Pak Andi']);
});

it('hanya menyertakan daftar yang sesuai jenis laporan', function () {
    Sanctum::actingAs(User::factory()->create());
    Kelas::factory()->create();

    $this->getJson('/api/v1/laporan/siswa-per-kelas')->assertJsonMissingPath('data.0.guru')->assertJsonPath('data.0.siswa', []);
    $this->getJson('/api/v1/laporan/guru-per-kelas')->assertJsonMissingPath('data.0.siswa')->assertJsonPath('data.0.guru', []);
});

it('memaginasi 10 Kelas per halaman tanpa Kelas ganda', function () {
    Sanctum::actingAs(User::factory()->create());
    Kelas::factory()->count(12)->create();

    $hal1 = $this->getJson('/api/v1/laporan/siswa-per-kelas')->assertJsonCount(10, 'data')->assertJsonPath('meta.total', 12)->json('data.*.id');
    $hal2 = $this->getJson('/api/v1/laporan/siswa-per-kelas?page=2')->assertJsonCount(2, 'data')->json('data.*.id');

    expect(array_intersect($hal1, $hal2))->toBe([]);
});

it('membatasi laporan Siswa ke Kelasnya sendiri', function () {
    $user = User::factory()->siswa()->create();
    Kelas::factory()->count(3)->create();
    Sanctum::actingAs($user);

    $this->getJson('/api/v1/laporan/siswa-per-kelas')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $user->siswa->kelas_id)
        ->assertJsonPath('judul', 'Teman Sekelas');
});

it('mengembalikan 404 untuk jenis laporan tak dikenal dan 422 untuk page tidak valid', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->getJson('/api/v1/laporan/tidak-ada')->assertNotFound();
    $this->getJson('/api/v1/laporan/siswa-per-kelas?page=abc')->assertUnprocessable()->assertJsonStructure(['message', 'errors' => ['page']]);
});

it('menyajikan ringkasan data aktif untuk semua peran', function (string $state) {
    Sanctum::actingAs(User::factory()->{$state}()->create());
    Guru::factory()->create()->delete();

    $this->getJson('/api/v1/ringkasan')
        ->assertOk()
        ->assertJsonStructure(['data' => ['total_siswa', 'total_kelas', 'total_guru']])
        ->assertJsonPath('data.total_guru', $state === 'guru' ? 1 : 0);
})->with(['guru', 'siswa']);
