<?php

use App\Models\User;

/*
| SEC-06: Host header harus milik APP_URL (production), dan tujuan setelah login (url.intended)
| tidak boleh mengarah ke domain lain.
*/

function hostAplikasi(): string
{
    return (string) parse_url((string) config('app.url'), PHP_URL_HOST);
}

it('menolak Host header asing di production', function (string $url) {
    $this->app['env'] = 'production';

    $this->get($url)->assertBadRequest();
})->with([
    'domain lain' => 'http://penyerang.test/login',
    'domain mirip' => 'http://localhost.penyerang.test/login',
]);

it('menolak Host asing di API dengan pesan Bahasa Indonesia', function () {
    $this->app['env'] = 'production';

    $this->getJson('http://penyerang.test/api/v1/kelas')
        ->assertBadRequest()
        ->assertExactJson(['message' => 'Permintaan tidak valid.']);
});

it('menerima Host milik APP_URL di production', function () {
    $this->app['env'] = 'production';

    $this->get('http://'.hostAplikasi().'/login')->assertOk();
});

it('tidak membatasi Host di lingkungan pengembangan', function () {
    $this->get('http://penyerang.test/login')->assertOk();
});

it('membuang tujuan login yang berada di domain lain', function () {
    $siswa = User::factory()->siswa()->create();

    $this->withSession(['url.intended' => 'http://penyerang.test/portal/laporan/siswa-per-kelas'])
        ->post('http://'.hostAplikasi().'/login', ['email' => $siswa->email, 'password' => 'password'])
        ->assertRedirect(route('portal.dashboard'));
});

it('tetap memakai tujuan login di host yang sama', function () {
    $siswa = User::factory()->siswa()->create();
    $tujuan = 'http://'.hostAplikasi().'/portal/laporan/siswa-per-kelas';

    $this->withSession(['url.intended' => $tujuan])
        ->post('http://'.hostAplikasi().'/login', ['email' => $siswa->email, 'password' => 'password'])
        ->assertRedirect($tujuan);
});
