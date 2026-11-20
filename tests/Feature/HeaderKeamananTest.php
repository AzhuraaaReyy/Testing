<?php

use App\Http\Middleware\SecurityHeaders;
use App\Models\User;
use Illuminate\Testing\TestResponse;

/*
| SEC-03: header keamanan dikirim di semua pintu, termasuk respons error API.
*/

function assertHeaderKeamanan(TestResponse $response): TestResponse
{
    foreach (SecurityHeaders::HEADERS as $nama => $nilai) {
        $response->assertHeader($nama, $nilai);
    }

    return $response;
}

it('mengirim header keamanan di halaman login dan halaman terlindungi', function (string $url) {
    assertHeaderKeamanan($this->get($url));
})->with([
    'login portal' => '/login',
    'login Admin' => '/admin/login',
    'redirect tamu' => '/portal',
]);

it('mengirim header keamanan di panel Admin dan portal setelah login', function () {
    assertHeaderKeamanan($this->actingAs(User::factory()->create())->get('/admin'))->assertOk();
    assertHeaderKeamanan($this->actingAs(User::factory()->siswa()->create())->get(route('portal.dashboard')))->assertOk();
});

it('mengirim header keamanan di respons API, termasuk respons error', function () {
    assertHeaderKeamanan($this->getJson('/api/v1/tidak-ada'))->assertNotFound();
    assertHeaderKeamanan($this->postJson('/api/v1/login', []))->assertUnprocessable();
});

it('tidak mengirim HSTS di luar produksi atau lewat HTTP', function () {
    $this->get('/login')->assertHeaderMissing('Strict-Transport-Security');

    $this->app['env'] = 'production';
    $this->get('http://localhost/login')->assertHeaderMissing('Strict-Transport-Security');
});

it('mengirim HSTS di produksi lewat HTTPS', function () {
    $this->app['env'] = 'production';

    $this->get('https://localhost/login')->assertHeader('Strict-Transport-Security', SecurityHeaders::HSTS);
});
