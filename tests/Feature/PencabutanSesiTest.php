<?php

use App\Actions\DataMaster\SinkronAkunPortalAction;
use App\Models\Siswa;
use App\Models\User;
use App\Support\SesiLogin;
use Laravel\Sanctum\PersonalAccessToken;

/*
| SEC2-01: akun portal yang password-nya diganti Admin (mis. diambil alih) tidak boleh tetap
| dipakai lewat session portal atau token API lama.
*/

function siswaDenganSesiDanToken(): array
{
    $siswa = Siswa::factory()->create();
    User::factory()->siswa($siswa)->create(['email' => 'korban@sekolah.test']);

    test()->post('/login', ['email' => 'korban@sekolah.test', 'password' => 'password'])->assertRedirect(route('portal.dashboard'));
    test()->get('/portal')->assertOk();

    $token = test()->postJson('/api/v1/login', ['email' => 'korban@sekolah.test', 'password' => 'password'])->json('token');

    return [$siswa, $token];
}

function gantiAkun(Siswa $siswa, array $akun): void
{
    app(SinkronAkunPortalAction::class)->handle($siswa->fresh(), $akun);
    app('auth')->forgetGuards();
}

/**
 * Guard Sanctum memeriksa guard "web" lebih dulu; di test, session portal tersimpan di memori
 * aplikasi yang sama. Dibuang agar yang diuji benar-benar token (seperti HTTP sungguhan).
 */
function tanpaSesiWeb(): void
{
    app('session')->flush();
    app('auth')->forgetGuards();
}

it('mengakhiri session portal lama setelah password diganti', function () {
    [$siswa] = siswaDenganSesiDanToken();

    gantiAkun($siswa, ['email' => 'korban@sekolah.test', 'password' => 'PasswordBaru123']);

    // Cookie penanda dibawa browser sungguhan; klien test perlu mengirimnya eksplisit.
    $this->withCookie(SesiLogin::COOKIE, '1')->get('/portal')->assertRedirect(route('login'));
    $this->assertGuest();
    $this->get(route('login'))->assertSee(SesiLogin::PESAN);
});

it('mencabut token API lama setelah password diganti', function () {
    [$siswa, $token] = siswaDenganSesiDanToken();

    gantiAkun($siswa, ['email' => 'korban@sekolah.test', 'password' => 'PasswordBaru123']);
    tanpaSesiWeb();

    $this->withToken($token)->getJson('/api/v1/me')->assertUnauthorized();
    expect(PersonalAccessToken::query()->count())->toBe(0);
});

it('tetap bisa login dengan password baru', function () {
    [$siswa] = siswaDenganSesiDanToken();

    gantiAkun($siswa, ['email' => 'korban@sekolah.test', 'password' => 'PasswordBaru123']);

    $this->post('/login', ['email' => 'korban@sekolah.test', 'password' => 'PasswordBaru123'])
        ->assertRedirect(route('portal.dashboard'));
});

it('tidak mencabut session maupun token bila password tidak diganti', function () {
    [$siswa, $token] = siswaDenganSesiDanToken();

    gantiAkun($siswa, ['email' => 'korban@sekolah.test', 'password' => null]);

    $this->get('/portal')->assertOk();

    tanpaSesiWeb();
    $this->withToken($token)->getJson('/api/v1/me')->assertOk();
});

it('menghapus token API saat akun portal dihapus', function () {
    [$siswa] = siswaDenganSesiDanToken();

    gantiAkun($siswa, ['email' => null]);

    expect(User::query()->where('email', 'korban@sekolah.test')->exists())->toBeFalse()
        ->and(PersonalAccessToken::query()->count())->toBe(0);
});
