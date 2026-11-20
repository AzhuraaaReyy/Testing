<?php

use App\Actions\Auth\AuthenticateUserAction;
use App\Actions\Auth\LogoutUserAction;
use App\Models\User;
use Illuminate\Testing\TestResponse;

/*
| Autentikasi API v1 (Bearer token). Aturan login sama dengan web (PRD 4.4–4.5, 22.2).
*/

function loginApi(string $email, string $password = 'password'): TestResponse
{
    return test()->postJson('/api/v1/login', ['email' => $email, 'password' => $password]);
}

it('menerbitkan Bearer token untuk Admin, Guru, dan Siswa', function (Closure $buat) {
    $user = $buat();

    loginApi($user->email)
        ->assertOk()
        ->assertJsonStructure(['token', 'token_type', 'user' => ['id', 'name', 'email', 'role', 'siswa_id', 'guru_id']])
        ->assertJsonPath('token_type', 'Bearer')
        ->assertJsonPath('user.role', $user->role->value);
})->with([
    'admin' => [fn () => User::factory()->create()],
    'guru' => [fn () => User::factory()->guru()->create()],
    'siswa' => [fn () => User::factory()->siswa()->create()],
]);

it('memakai token untuk mengakses /me lalu mencabutnya saat logout', function () {
    $user = User::factory()->create();
    $token = loginApi($user->email)->json('token');

    $this->withToken($token)->getJson('/api/v1/me')->assertOk()->assertJsonPath('data.email', $user->email);

    $this->withToken($token)->postJson('/api/v1/logout')
        ->assertOk()
        ->assertJsonPath('message', LogoutUserAction::BERHASIL);

    // Token dicabut di database; permintaan berikutnya dengan token yang sama ditolak.
    app('auth')->forgetGuards();
    $this->withToken($token)->getJson('/api/v1/me')->assertUnauthorized();
});

it('menolak kredensial salah dengan pesan umum (422)', function () {
    $user = User::factory()->create();

    loginApi($user->email, 'salah')
        ->assertUnprocessable()
        ->assertJsonPath('errors.email.0', AuthenticateUserAction::GAGAL);

    loginApi('tidak-ada@sekolah.test')->assertJsonPath('errors.email.0', AuthenticateUserAction::GAGAL);
});

it('memvalidasi payload login dengan pesan Bahasa Indonesia', function () {
    $this->postJson('/api/v1/login', ['email' => 'bukan-email'])
        ->assertUnprocessable()
        ->assertJsonPath('errors.email.0', 'Format email tidak valid.')
        ->assertJsonPath('errors.password.0', 'Password wajib diisi.');
});

it('membatasi login setelah tiga kegagalan', function () {
    $user = User::factory()->create();

    foreach (range(1, AuthenticateUserAction::MAX_ATTEMPTS) as $_) {
        loginApi($user->email, 'salah');
    }

    loginApi($user->email)
        ->assertUnprocessable()
        ->assertJsonPath('errors.email.0', fn (string $pesan): bool => str_starts_with($pesan, 'Terlalu banyak percobaan login.'));
});

it('menolak akun yang profilnya di Trash, baik saat login maupun dengan token lama', function () {
    $user = User::factory()->siswa()->create();
    $token = loginApi($user->email)->json('token');

    $user->siswa->delete();

    loginApi($user->email)->assertUnprocessable();

    app('auth')->forgetGuards();
    $this->withToken($token)->getJson('/api/v1/me')->assertUnauthorized();
    expect($user->tokens()->count())->toBe(0);
});

it('menolak permintaan tanpa token (401)', function () {
    $this->getJson('/api/v1/me')->assertUnauthorized();
});
