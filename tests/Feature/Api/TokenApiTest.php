<?php

use App\Actions\Auth\BuatTokenApiAction;
use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;

/*
| SEC2-06: token API berprefix agar dikenali pemindai rahasia.
| SEC2-07: token aktif per akun dibatasi; login berikutnya mencabut token tertua.
*/

function loginApiToken(User $user): string
{
    return (string) test()->postJson('/api/v1/login', ['email' => $user->email, 'password' => 'password'])
        ->assertOk()
        ->json('token');
}

it('membuat token berprefix "sekolah_" yang bisa dipakai', function () {
    $token = loginApiToken(User::factory()->create());

    expect($token)->toMatch('/^\d+\|sekolah_[A-Za-z0-9]{40}[0-9a-f]{8}$/');
    $this->withToken($token)->getJson('/api/v1/me')->assertOk();
});

it('tetap menerima token lama tanpa prefix', function () {
    config(['sanctum.token_prefix' => '']);
    $user = User::factory()->create();
    $lama = $user->createToken('api')->plainTextToken;
    config(['sanctum.token_prefix' => 'sekolah_']);

    $this->withToken($lama)->getJson('/api/v1/me')->assertOk();
});

it('membatasi token aktif per akun dan mencabut yang tertua', function () {
    $user = User::factory()->siswa()->create();

    $token = [];
    foreach (range(1, BuatTokenApiAction::MAKS_TOKEN + 2) as $_) {
        $token[] = loginApiToken($user);
    }

    expect(PersonalAccessToken::query()->where('tokenable_id', $user->id)->count())->toBe(BuatTokenApiAction::MAKS_TOKEN);

    // Dua token tertua dicabut; token terbaru tetap berlaku.
    $this->withToken($token[0])->getJson('/api/v1/me')->assertUnauthorized();
    app('auth')->forgetGuards();
    $this->withToken($token[1])->getJson('/api/v1/me')->assertUnauthorized();
    app('auth')->forgetGuards();
    $this->withToken(end($token))->getJson('/api/v1/me')->assertOk();
});

it('tidak mencabut token milik akun lain', function () {
    $lain = User::factory()->create();
    $tokenLain = loginApiToken($lain);
    $user = User::factory()->siswa()->create();

    foreach (range(1, BuatTokenApiAction::MAKS_TOKEN + 1) as $_) {
        loginApiToken($user);
    }

    app('auth')->forgetGuards();
    $this->withToken($tokenLain)->getJson('/api/v1/me')->assertOk();
});
