<?php

namespace Database\Seeders;

use App\Enums\Tingkat;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Data contoh pengembangan untuk Siswa, Kelas, dan Guru.
 * Sengaja menyertakan kasus tepi PRD: Kelas tanpa Siswa/Guru, Guru di banyak Kelas,
 * Guru tanpa NIP, serta data di Trash yang tidak boleh tampil di laporan.
 */
class SekolahSeeder extends Seeder
{
    public function run(): void
    {
        $kelas = collect([
            [Tingkat::X, 'IPA', 'X IPA 1'], [Tingkat::X, 'IPA', 'X IPA 2'],
            [Tingkat::X, 'IPS', 'X IPS 1'], [Tingkat::X, 'IPS', 'X IPS 2'],
            [Tingkat::X, 'Bahasa', 'X Bahasa 1'],
            [Tingkat::XI, 'IPA', 'XI IPA 1'], [Tingkat::XI, 'IPA', 'XI IPA 2'],
            [Tingkat::XI, 'IPS', 'XI IPS 1'], [Tingkat::XI, 'Bahasa', 'XI Bahasa 1'],
            [Tingkat::XII, 'IPA', 'XII IPA 1'], [Tingkat::XII, 'IPA', 'XII IPA 2'],
            [Tingkat::XII, 'IPS', 'XII IPS 1'], [Tingkat::XII, 'IPS', 'XII IPS 2'],
            [Tingkat::XII, 'Bahasa', 'XII Bahasa 1'],
        ])->map(fn (array $row): Kelas => Kelas::factory()->create([
            'tingkat' => $row[0],
            'jurusan' => $row[1],
            'nama_kelas' => $row[2],
        ]));

        // Dua Kelas dibiarkan tanpa Siswa untuk memperlihatkan "Belum ada data".
        $kelasBerisi = $kelas->reject(fn (Kelas $k): bool => in_array($k->nama_kelas, ['X Bahasa 1', 'XII Bahasa 1'], true));

        $kelasBerisi->each(fn (Kelas $k) => Siswa::factory()
            ->count(fake()->numberBetween(6, 14))
            ->for($k)
            ->create());

        // Satu Kelas tanpa Guru.
        $kelasBerGuru = $kelas->reject(fn (Kelas $k): bool => $k->nama_kelas === 'XI Bahasa 1');

        Guru::factory()->count(18)->create()->each(function (Guru $guru) use ($kelasBerGuru): void {
            $guru->kelas()->attach($kelasBerGuru->random(fake()->numberBetween(1, 3))->pluck('id'));
        });

        // Akun portal contoh (PRD v1.3). Password: "password" — hanya untuk pengembangan lokal.
        User::factory()->guru(Guru::query()->has('kelas')->orderBy('id')->firstOrFail())->create([
            'name' => 'Guru Contoh',
            'email' => 'guru@sekolah.test',
        ]);
        User::factory()->siswa(Siswa::query()->orderBy('id')->firstOrFail())->create([
            'name' => 'Siswa Contoh',
            'email' => 'siswa@sekolah.test',
        ]);

        // Data di Trash: relasi dilepas lalu soft delete (PRD 5.7, 7.6).
        Siswa::factory()->count(3)->for($kelas->first())->create()->each(function (Siswa $siswa): void {
            $siswa->update(['kelas_id' => null]);
            $siswa->delete();
        });

        Guru::factory()->create()->delete();
        Kelas::factory()->create(['nama_kelas' => 'X Arsip', 'tingkat' => Tingkat::X, 'jurusan' => 'IPA'])->delete();
    }
}
