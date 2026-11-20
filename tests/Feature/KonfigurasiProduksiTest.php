<?php

use App\Models\User;
use Illuminate\Session\EncryptedStore;

/*
| SEC-08 & SEC-10: konfigurasi bawaan aman untuk production, dan .env.example memuat panduannya.
*/

/**
 * Membaca config/{file}.php dengan variabel lingkungan tertentu, lalu memulihkannya.
 *
 * @param  array<string, string|null>  $env  null = variabel tidak diset.
 * @return array<string, mixed>
 */
function configDenganEnv(string $file, array $env): array
{
    $semula = [];

    foreach ($env as $nama => $nilai) {
        $semula[$nama] = [$_ENV[$nama] ?? null, $_SERVER[$nama] ?? null, getenv($nama)];
        unset($_ENV[$nama], $_SERVER[$nama]);
        putenv($nama);

        if ($nilai !== null) {
            $_ENV[$nama] = $_SERVER[$nama] = $nilai;
            putenv("{$nama}={$nilai}");
        }
    }

    try {
        return require config_path("{$file}.php");
    } finally {
        foreach ($semula as $nama => [$env, $server, $put]) {
            unset($_ENV[$nama], $_SERVER[$nama]);
            putenv($nama);
            if ($env !== null) {
                $_ENV[$nama] = $env;
            }
            if ($server !== null) {
                $_SERVER[$nama] = $server;
            }
            if ($put !== false) {
                putenv("{$nama}={$put}");
            }
        }
    }
}

it('mengirim cookie session hanya lewat HTTPS di production secara bawaan', function () {
    $session = configDenganEnv('session', ['APP_ENV' => 'production', 'SESSION_SECURE_COOKIE' => null]);

    expect($session['secure'])->toBeTrue()
        ->and($session['http_only'])->toBeTrue()
        ->and($session['same_site'])->toBe('lax');
});

it('tidak memaksa cookie Secure di pengembangan lokal (HTTP)', function () {
    expect(configDenganEnv('session', ['APP_ENV' => 'local', 'SESSION_SECURE_COOKIE' => null])['secure'])->toBeFalse();
});

it('tetap bisa diatur eksplisit lewat SESSION_SECURE_COOKIE', function () {
    expect(configDenganEnv('session', ['APP_ENV' => 'production', 'SESSION_SECURE_COOKIE' => 'false'])['secure'])->toBeFalse();
});

it('mengenkripsi isi session di production secara bawaan (SEC2-09)', function () {
    expect(configDenganEnv('session', ['APP_ENV' => 'production', 'SESSION_ENCRYPT' => null])['encrypt'])->toBeTrue()
        ->and(configDenganEnv('session', ['APP_ENV' => 'local', 'SESSION_ENCRYPT' => null])['encrypt'])->toBeFalse()
        ->and(configDenganEnv('session', ['APP_ENV' => 'production', 'SESSION_ENCRYPT' => 'false'])['encrypt'])->toBeFalse();
});

it('tidak menimpa bawaan enkripsi session lewat .env.example', function () {
    // .env production biasanya disalin dari .env.example; nilai eksplisit "false" akan menimpa bawaan aman.
    expect((string) file_get_contents(base_path('.env.example')))->not->toMatch('/^SESSION_ENCRYPT=/m');
});

it('mematikan mode debug bila APP_DEBUG tidak diset', function () {
    expect(configDenganEnv('app', ['APP_DEBUG' => null])['debug'])->toBeFalse();
});

it('memuat panduan konfigurasi production di .env.example', function () {
    $contoh = (string) file_get_contents(base_path('.env.example'));

    expect($contoh)
        ->toContain('#   APP_DEBUG=false')
        ->toContain('#   SESSION_SECURE_COOKIE=true')
        ->toContain('php artisan schedule:run')
        ->toContain('sekolah:buat-admin');
});

it('tetap bisa login portal saat isi session dienkripsi', function () {
    config(['session.encrypt' => true]);
    $siswa = User::factory()->siswa()->create();

    $this->post('/login', ['email' => $siswa->email, 'password' => 'password'])->assertRedirect(route('portal.dashboard'));
    $this->get('/portal')->assertOk();

    expect(app('session')->driver())->toBeInstanceOf(EncryptedStore::class);
});
