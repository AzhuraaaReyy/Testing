<?php

use App\Actions\Auth\AuthenticateUserAction;
use App\Enums\PintuLogin;
use App\Models\User;
use Carbon\CarbonInterval;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Sleep;
use Illuminate\Validation\ValidationException;

/*
| SEC-02: waktu respons login gagal tidak boleh membedakan email terdaftar dan tidak terdaftar.
| Timebox menambah jeda hingga durasi minimum; Sleep::fake() mencatat jeda tanpa benar-benar menunggu.
*/

function cobaLogin(string $email, string $password): ?User
{
    try {
        return app(AuthenticateUserAction::class)->verifikasi($email, $password, '203.0.113.9', PintuLogin::Api);
    } catch (ValidationException) {
        return null;
    }
}

/** Total waktu (nyata + jeda Timebox) dalam mikrodetik untuk satu percobaan. */
function durasiLogin(string $email, string $password): float
{
    Sleep::fake();
    $mulai = microtime(true);
    cobaLogin($email, $password);
    $nyata = (microtime(true) - $mulai) * 1_000_000;

    $jeda = 0;
    Sleep::assertSlept(function (CarbonInterval $durasi) use (&$jeda) {
        $jeda += $durasi->totalMicroseconds;

        return true;
    }, 1);

    return $nyata + $jeda;
}

it('memberi durasi minimum yang sama untuk email terdaftar dan tidak terdaftar', function (string $email) {
    User::factory()->siswa()->create(['email' => 'siswa@sekolah.test']);

    expect(durasiLogin($email, 'password-salah'))->toBeGreaterThanOrEqual(AuthenticateUserAction::TIMEBOX_MIKRODETIK);
})->with([
    'email terdaftar' => 'siswa@sekolah.test',
    'email tidak terdaftar' => 'tidak-ada@sekolah.test',
]);

it('tetap menjalankan operasi hash untuk email yang tidak terdaftar', function () {
    Sleep::fake();
    Hash::shouldReceive('make')->once()->with('password-salah')->andReturn('hash-tiruan');

    expect(cobaLogin('tidak-ada@sekolah.test', 'password-salah'))->toBeNull();
});

it('tidak menahan login yang berhasil', function () {
    $user = User::factory()->siswa()->create();
    Sleep::fake();

    expect(cobaLogin($user->email, 'password')?->is($user))->toBeTrue();

    Sleep::assertNeverSlept();
});
