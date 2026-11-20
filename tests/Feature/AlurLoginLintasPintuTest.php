<?php

use App\Actions\Auth\AuthenticateUserAction;
use App\Enums\Role;
use App\Filament\Pages\Auth\Login;
use App\Models\User;
use Filament\Notifications\Notification;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

/*
| Alur login lintas pintu (portal Guru/Siswa, panel Admin, API). Satu browser memegang satu
| session, sehingga perpindahan antar-pintu harus aman dan jelas bagi pengguna.
*/

function loginAdmin(string $email, string $password = 'password'): Testable
{
    return Livewire::test(Login::class)->fillForm(['email' => $email, 'password' => $password])->call('authenticate');
}

it('tidak mengunci portal karena percobaan di pintu Admin (penghitung per pintu)', function () {
    $siswa = User::factory()->siswa()->create();

    foreach (range(1, AuthenticateUserAction::MAX_ATTEMPTS) as $_) {
        loginAdmin($siswa->email)->assertHasFormErrors(['email' => AuthenticateUserAction::GAGAL]);
    }

    // Pintu Admin terkunci untuk akun ini…
    loginAdmin($siswa->email)->assertSee('Terlalu banyak percobaan login');

    // …tetapi portal (pintu yang benar) tetap bisa dipakai.
    $this->post('/login', ['email' => $siswa->email, 'password' => 'password'])
        ->assertRedirect(route('portal.dashboard'));
    $this->assertAuthenticatedAs($siswa);
});

it('tetap membatasi tiga kegagalan di setiap pintu', function () {
    $siswa = User::factory()->siswa()->create();

    foreach (range(1, AuthenticateUserAction::MAX_ATTEMPTS) as $_) {
        $this->post('/login', ['email' => $siswa->email, 'password' => 'salah']);
    }

    $this->post('/login', ['email' => $siswa->email, 'password' => 'password'])
        ->assertSessionHasErrors(['email' => 'Terlalu banyak percobaan login. Silakan coba lagi dalam 15 menit.']);
    $this->assertGuest();
});

it('tidak membawa siswa ke halaman Admin yang tadinya diminta (bukan 403)', function () {
    $siswa = User::factory()->siswa()->create();

    $this->get('/admin/siswa')->assertRedirect(filament()->getLoginUrl());

    $this->post('/login', ['email' => $siswa->email, 'password' => 'password'])
        ->assertRedirect(route('portal.dashboard'));
});

it('tetap kembali ke halaman yang diminta bila masih di wilayah perannya (PRD 4.6 poin 4)', function () {
    $siswa = User::factory()->siswa()->create();
    $tujuan = route('portal.laporan.show', 'siswa-per-kelas');

    $this->get($tujuan)->assertRedirect(route('login'));

    $this->post('/login', ['email' => $siswa->email, 'password' => 'password'])->assertRedirect($tujuan);
});

it('tidak membawa Admin ke halaman portal yang tadinya diminta', function () {
    $admin = User::factory()->create();

    $this->get(route('portal.dashboard'))->assertRedirect(route('login'));

    loginAdmin($admin->email)->assertRedirect(filament()->getUrl());
});

it('menjelaskan kepada Admin yang membuka login portal bahwa ia perlu logout dulu', function () {
    $this->actingAs(User::factory()->create())
        ->get('/login')
        ->assertRedirect(filament()->getUrl());

    Notification::assertNotified(Role::Admin->pesanSudahLogin());
});

it('mengarahkan Guru/Siswa yang membuka login Admin ke portal dengan penjelasan, bukan 403', function (string $state) {
    $user = User::factory()->{$state}()->create();
    $this->actingAs($user);

    Livewire::test(Login::class)->assertRedirect(route('portal.dashboard'));

    $this->get(route('portal.dashboard'))
        ->assertOk()
        ->assertSeeText($user->role->pesanSudahLogin());
})->with(['guru', 'siswa']);

it('menampilkan petunjuk ke portal di halaman login Admin', function () {
    $this->get(filament()->getLoginUrl())
        ->assertOk()
        ->assertSeeText('Guru atau Siswa?')
        ->assertSee('href="'.route('login').'"', escape: false);
});

it('memisahkan penghitung API dari portal dan Admin', function () {
    $siswa = User::factory()->siswa()->create();

    foreach (range(1, AuthenticateUserAction::MAX_ATTEMPTS) as $_) {
        $this->postJson('/api/v1/login', ['email' => $siswa->email, 'password' => 'salah']);
    }

    $this->postJson('/api/v1/login', ['email' => $siswa->email, 'password' => 'password'])->assertUnprocessable();
    $this->post('/login', ['email' => $siswa->email, 'password' => 'password'])->assertRedirect(route('portal.dashboard'));
});
