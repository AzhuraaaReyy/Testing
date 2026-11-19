<?php

use App\Actions\Auth\AuthenticatePortalUserAction;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Testing\TestResponse;

beforeEach(fn () => RateLimiter::clear('portal-login:siswa@sekolah.test|127.0.0.1'));

function loginPortal(string $email, string $password = 'password'): TestResponse
{
    return test()->post(route('login.store'), ['email' => $email, 'password' => $password]);
}

it('menampilkan halaman login portal untuk tamu', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertSee('Masuk ke Portal')
        ->assertSee('Masuk melalui panel Admin');
});

it('mengarahkan tamu yang membuka portal ke login portal', function () {
    $this->get(route('portal.dashboard'))->assertRedirect(route('login'));
});

it('meloginkan Guru dan Siswa lalu mengarahkan ke Dashboard portal', function (string $state) {
    $user = User::factory()->{$state}()->create();

    loginPortal($user->email)->assertRedirect(route('portal.dashboard'));

    $this->assertAuthenticatedAs($user);
})->with(['guru', 'siswa']);

it('menolak akun Admin di login portal dengan pesan umum', function () {
    $admin = User::factory()->create();

    loginPortal($admin->email)->assertSessionHasErrors(['email' => AuthenticatePortalUserAction::GAGAL]);

    $this->assertGuest();
});

it('menolak kredensial salah dengan pesan umum', function () {
    $user = User::factory()->siswa()->create();

    loginPortal($user->email, 'salah')->assertSessionHasErrors(['email' => AuthenticatePortalUserAction::GAGAL]);
    loginPortal('tidak-ada@sekolah.test')->assertSessionHasErrors(['email' => AuthenticatePortalUserAction::GAGAL]);
});

it('memvalidasi format email dengan pesan PRD', function () {
    loginPortal('bukan-email')->assertSessionHasErrors(['email' => 'Format email tidak valid.']);
});

it('membatasi login 15 menit setelah 3 kegagalan dan mereset setelah berhasil', function () {
    $user = User::factory()->siswa()->create(['email' => 'siswa@sekolah.test']);

    loginPortal($user->email, 'salah');
    loginPortal($user->email, 'salah');
    loginPortal($user->email, 'salah');

    loginPortal($user->email)->assertSessionHasErrors(['email' => 'Terlalu banyak percobaan login. Silakan coba lagi dalam 15 menit.']);
    $this->assertGuest();

    $this->travel(16)->minutes();

    loginPortal($user->email)->assertRedirect(route('portal.dashboard'));
    expect(RateLimiter::attempts('portal-login:siswa@sekolah.test|127.0.0.1'))->toBe(0);
});

it('menolak login Siswa yang berada di Trash', function () {
    $siswa = Siswa::factory()->create();
    $user = User::factory()->siswa($siswa)->create();
    $siswa->update(['kelas_id' => null]);
    $siswa->delete();

    loginPortal($user->email)->assertSessionHasErrors(['email' => AuthenticatePortalUserAction::GAGAL]);
});

it('logout mengakhiri session dan menampilkan pesan keberhasilan', function () {
    $user = User::factory()->guru()->create();

    $this->actingAs($user)->post(route('logout'))->assertRedirect(route('login'));
    $this->assertGuest();

    $this->get(route('login'))->assertSee('Anda berhasil keluar dari sistem.');
});

it('mengarahkan pengguna yang sudah login dari halaman login ke berandanya', function () {
    $this->actingAs(User::factory()->siswa()->create())
        ->get(route('login'))
        ->assertRedirect(route('portal.dashboard'));
});
