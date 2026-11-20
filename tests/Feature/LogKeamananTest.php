<?php

use App\Actions\Auth\AuthenticateUserAction;
use App\Actions\DataMaster\SinkronAkunPortalAction;
use App\Models\Siswa;
use App\Models\User;
use App\Support\LogKeamanan;

/*
| SEC2-04: kejadian keamanan tercatat di channel "security", tanpa password dan dengan email disamarkan.
*/

beforeEach(function () {
    $this->logKeamanan = storage_path('framework/testing/security-uji.log');
    @unlink($this->logKeamanan);
    config(['logging.channels.security' => ['driver' => 'single', 'path' => $this->logKeamanan, 'level' => 'info']]);
});

afterEach(fn () => @unlink($this->logKeamanan));

function isiLogKeamanan(): string
{
    return is_file(test()->logKeamanan) ? (string) file_get_contents(test()->logKeamanan) : '';
}

it('menyamarkan email', function (string $email, string $hasil) {
    expect(LogKeamanan::samarkanEmail($email))->toBe($hasil);
})->with([
    ['siswa@sekolah.test', 'si***@sekolah.test'],
    ['A@sekolah.test', 'a***@sekolah.test'],
    ['bukan-email', 'bu***'],
]);

it('mencatat login gagal tanpa password dan dengan email disamarkan', function () {
    $this->post('/login', ['email' => 'korban@sekolah.test', 'password' => 'RahasiaSekali99']);

    expect(isiLogKeamanan())
        ->toContain('.WARNING: login.gagal')
        ->toContain('"pintu":"portal"')
        ->toContain('"email":"ko***@sekolah.test"')
        ->toContain('"ip":"127.0.0.1"')
        ->not->toContain('korban@sekolah.test')
        ->not->toContain('RahasiaSekali99');
});

it('mencatat penguncian login per email', function () {
    foreach (range(1, AuthenticateUserAction::MAX_ATTEMPTS + 1) as $_) {
        $this->postJson('/api/v1/login', ['email' => 'korban@sekolah.test', 'password' => 'salah']);
    }

    expect(isiLogKeamanan())->toContain('login.terkunci')->toContain('"batas":"email"')->toContain('"pintu":"api"');
});

it('mencatat login berhasil', function () {
    $user = User::factory()->siswa()->create();

    $this->post('/login', ['email' => $user->email, 'password' => 'password']);

    expect(isiLogKeamanan())->toContain('.INFO: login.berhasil')->toContain('"user_id":'.$user->id);
});

it('mencatat akses lintas peran yang ditolak', function () {
    $siswa = User::factory()->siswa()->create();

    $this->actingAs($siswa)->getJson('/api/v1/siswa')->assertForbidden();

    expect(isiLogKeamanan())->toContain('akses.ditolak')->toContain('"peran":"siswa"')->toContain('"path":"api/v1/siswa"');
});

it('mencatat penggantian password dan penghapusan akun portal oleh Admin', function () {
    $admin = User::factory()->create();
    $siswa = Siswa::factory()->create();
    $akun = User::factory()->siswa($siswa)->create();
    $this->actingAs($admin);

    app(SinkronAkunPortalAction::class)->handle($siswa->fresh(), ['email' => $akun->email, 'password' => 'PasswordBaru123']);
    app(SinkronAkunPortalAction::class)->handle($siswa->fresh(), ['email' => null]);

    expect(isiLogKeamanan())
        ->toContain('.NOTICE: akun.password_diganti {"user_id":'.$akun->id.',"oleh":'.$admin->id.'}')
        ->toContain('.NOTICE: akun.dihapus {"user_id":'.$akun->id.',"oleh":'.$admin->id.'}')
        ->not->toContain('PasswordBaru123');
});
