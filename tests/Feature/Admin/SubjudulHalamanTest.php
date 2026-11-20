<?php

use App\Enums\JenisLaporan;
use App\Filament\Pages\Laporan;
use App\Filament\Resources\Siswa\Pages\ListSiswa;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Livewire\Livewire;

beforeEach(fn () => $this->actingAs(User::factory()->create()));

it('menampilkan deskripsi di bawah judul setiap menu', function (Closure $url, string $deskripsi) {
    $this->get($url())->assertOk()->assertSeeText($deskripsi);
})->with([
    'Dashboard' => [fn (): string => '/admin', 'Ringkasan data Siswa, Kelas, dan Guru.'],
    'Siswa' => [fn (): string => '/admin/siswa', 'Kelola data Siswa beserta Kelasnya.'],
    'Kelas' => [fn (): string => '/admin/kelas', 'Kelola data Kelas berdasarkan Tingkat dan Jurusan.'],
    'Guru' => [fn (): string => '/admin/guru', 'Kelola data Guru beserta Kelas yang diajar.'],
    'Laporan Siswa per Kelas' => [fn (): string => Laporan::getUrl(['jenis' => JenisLaporan::SiswaPerKelas]), JenisLaporan::SiswaPerKelas->deskripsi()],
    'Laporan Guru per Kelas' => [fn (): string => Laporan::getUrl(['jenis' => JenisLaporan::GuruPerKelas]), JenisLaporan::GuruPerKelas->deskripsi()],
    'Laporan Gabungan' => [fn (): string => Laporan::getUrl(['jenis' => JenisLaporan::Gabungan]), JenisLaporan::Gabungan->deskripsi()],
]);

it('menampilkan deskripsi di halaman Tambah, Edit, dan Detail Data Master', function (string $slug, string $label, Closure $buat) {
    $id = $buat()->id;

    $this->get("/admin/{$slug}/create")->assertSeeText("Lengkapi data {$label} baru. Kolom bertanda * wajib diisi.");
    $this->get("/admin/{$slug}/{$id}/edit")->assertSeeText("Perbarui data {$label}. Kolom bertanda * wajib diisi.");
    $this->get("/admin/{$slug}/{$id}")->assertSeeText("Informasi lengkap data {$label}.");
})->with([
    ['siswa', 'Siswa', fn () => Siswa::factory()->create()],
    ['kelas', 'Kelas', fn () => Kelas::factory()->create()],
    ['guru', 'Guru', fn () => Guru::factory()->create()],
]);

it('mengganti deskripsi saat tab Trash dibuka', function () {
    Livewire::test(ListSiswa::class)
        ->assertSeeText('Kelola data Siswa beserta Kelasnya.')
        ->set('activeTab', 'trash')
        ->assertSeeText('Data Siswa yang telah dihapus. Pulihkan untuk mengaktifkannya kembali.')
        ->assertDontSeeText('Kelola data Siswa beserta Kelasnya.');
});
