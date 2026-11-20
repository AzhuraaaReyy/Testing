<?php

use App\Enums\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

it('membuat akun Admin yang bisa login ke panel', function () {
    $this->artisan('sekolah:buat-admin', ['--nama' => 'Kepala TU', '--email' => 'tu@sekolah.sch.id'])
        ->expectsQuestion('Password (min. 8 karakter, memuat huruf dan angka)', 'rahasia123')
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
        ->expectsQuestion('Password (min. 8 karakter, memuat huruf dan angka)', 'rahasia123')
        ->expectsQuestion('Ulangi password', 'rahasia123')
        ->assertSuccessful();

    expect(User::query()->where('email', 'admin2@sekolah.sch.id')->exists())->toBeTrue();
});

it('menolak data tidak valid tanpa membuat akun', function (array $opsi, string $password, string $ulang, string $pesan) {
    User::factory()->create(['email' => 'ada@sekolah.sch.id']);

    $this->artisan('sekolah:buat-admin', $opsi)
        ->expectsQuestion('Password (min. 8 karakter, memuat huruf dan angka)', $password)
        ->expectsQuestion('Ulangi password', $ulang)
        ->expectsOutputToContain($pesan)
        ->assertFailed();

    expect(User::query()->count())->toBe(1);
})->with([
    'email dipakai' => [['--nama' => 'X', '--email' => 'ada@sekolah.sch.id'], 'rahasia123', 'rahasia123', 'Email sudah digunakan.'],
    'email tidak valid' => [['--nama' => 'X', '--email' => 'bukan-email'], 'rahasia123', 'rahasia123', 'Format Email tidak valid.'],
    'password pendek' => [['--nama' => 'X', '--email' => 'b@sekolah.sch.id'], 'pendek', 'pendek', 'Password minimal 8 karakter.'],
    'hanya angka' => [['--nama' => 'X', '--email' => 'b@sekolah.sch.id'], '12345678', '12345678', 'Password harus memuat minimal satu huruf.'],
    'tanpa angka' => [['--nama' => 'X', '--email' => 'b@sekolah.sch.id'], 'rahasiasekali', 'rahasiasekali', 'Password harus memuat minimal satu angka.'],
    'konfirmasi beda' => [['--nama' => 'X', '--email' => 'b@sekolah.sch.id'], 'rahasia123', 'rahasia999', 'Konfirmasi Password tidak cocok.'],
]);

/** Jawaban palsu Have I Been Pwned: "rahasia123" tercatat pernah bocor. */
function passwordBocor(): string
{
    // k-anonymity: hanya 5 karakter awal SHA-1 yang dikirim; jawabannya daftar sisa hash.
    $sha1 = strtoupper(sha1('rahasia123'));
    Http::fake(['api.pwnedpasswords.com/range/'.substr($sha1, 0, 5) => Http::response(substr($sha1, 5).':12345')]);

    return $sha1;
}

/*
| Dua test di bawah sengaja tidak menjalankan perintah artisan dengan APP_ENV=production:
| Laravel Prompts hanya memakai prompt yang dapat di-mock (expectsQuestion) bila environment
| "testing" atau OS Windows. Di Linux (CI) dengan env production, prompt interaktif asli dipakai
| dan gagal karena tidak ada terminal (NonInteractiveValidationException "Required.").
*/

it('memakai pengecekan password bocor di production (SEC-05)', function () {
    $sha1 = passwordBocor();
    $this->app['env'] = 'production';

    $validator = Validator::make(['password' => 'rahasia123'], ['password' => Password::defaults()]);

    expect($validator->errors()->first('password'))->toContain('pernah bocor dalam kebocoran data');
    Http::assertSent(fn ($request): bool => ! str_contains($request->url(), 'rahasia123') && ! str_contains($request->url(), $sha1));
});

it('menolak membuat Admin dengan password yang pernah bocor', function () {
    passwordBocor();
    // Aturan production (AppServiceProvider) tanpa mengubah environment; lihat catatan di atas.
    Password::defaults(fn (): Password => Password::min(8)->letters()->numbers()->uncompromised());

    $this->artisan('sekolah:buat-admin', ['--nama' => 'X', '--email' => 'b@sekolah.sch.id'])
        ->expectsQuestion('Password (min. 8 karakter, memuat huruf dan angka)', 'rahasia123')
        ->expectsQuestion('Ulangi password', 'rahasia123')
        ->expectsOutputToContain('pernah bocor dalam kebocoran data')
        ->assertFailed();

    expect(User::query()->count())->toBe(0);
});

it('tidak menghubungi layanan password bocor di luar production', function () {
    Http::preventStrayRequests();

    $this->artisan('sekolah:buat-admin', ['--nama' => 'X', '--email' => 'b@sekolah.sch.id'])
        ->expectsQuestion('Password (min. 8 karakter, memuat huruf dan angka)', 'rahasia123')
        ->expectsQuestion('Ulangi password', 'rahasia123')
        ->assertSuccessful();
});
