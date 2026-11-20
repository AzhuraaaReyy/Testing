<?php

use App\Enums\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('membuat akun Admin yang bisa login ke panel', function () {
    $this->artisan('sekolah:buat-admin', ['--nama' => 'Kepala TU', '--email' => 'tu@sekolah.sch.id'])
        ->expectsQuestion('Password (min. 8 karakter)', 'rahasia123')
        ->expectsQuestion('Ulangi password', 'rahasia123')
        ->assertSuccessful();

    $admin = User::query()->where('email', 'tu@sekolah.sch.id')->sole();

    expect($admin)->name->toBe('Kepala TU')->role->toBe(Role::Admin)
        ->and(Hash::check('rahasia123', $admin->password))->toBeTrue();
});

it('menanyakan nama dan email bila tidak diberikan lewat opsi', function () {
    $this->artisan('sekolah:buat-admin')
        ->expectsQuestion('Nama Admin', 'Admin Dua')
        ->expectsQuestion('Email', 'admin2@sekolah.sch.id')
        ->expectsQuestion('Password (min. 8 karakter)', 'rahasia123')
        ->expectsQuestion('Ulangi password', 'rahasia123')
        ->assertSuccessful();

    expect(User::query()->where('email', 'admin2@sekolah.sch.id')->exists())->toBeTrue();
});

it('menolak data tidak valid tanpa membuat akun', function (array $opsi, string $password, string $ulang, string $pesan) {
    User::factory()->create(['email' => 'ada@sekolah.sch.id']);

    $this->artisan('sekolah:buat-admin', $opsi)
        ->expectsQuestion('Password (min. 8 karakter)', $password)
        ->expectsQuestion('Ulangi password', $ulang)
        ->expectsOutputToContain($pesan)
        ->assertFailed();

    expect(User::query()->count())->toBe(1);
})->with([
    'email dipakai' => [['--nama' => 'X', '--email' => 'ada@sekolah.sch.id'], 'rahasia123', 'rahasia123', 'Email sudah digunakan.'],
    'email tidak valid' => [['--nama' => 'X', '--email' => 'bukan-email'], 'rahasia123', 'rahasia123', 'Format Email tidak valid.'],
    'password pendek' => [['--nama' => 'X', '--email' => 'b@sekolah.sch.id'], 'pendek', 'pendek', 'Password minimal 8 karakter.'],
    'konfirmasi beda' => [['--nama' => 'X', '--email' => 'b@sekolah.sch.id'], 'rahasia123', 'rahasia999', 'Konfirmasi Password tidak cocok.'],
]);
