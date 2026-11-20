<?php

use App\Http\Middleware\SecurityHeaders;
use App\Models\User;
use Illuminate\Support\Facades\Vite;
use Illuminate\Testing\TestResponse;

/*
| SEC-03: header keamanan dikirim di semua pintu, termasuk respons error API.
*/

function assertHeaderKeamanan(TestResponse $response): TestResponse
{
    foreach (SecurityHeaders::HEADERS as $nama => $nilai) {
        $response->assertHeader($nama, $nilai);
    }

    $response->assertHeader('Content-Security-Policy', SecurityHeaders::csp());

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

it('mendefinisikan seluruh directive CSP yang tidak punya fallback (ZAP 10055)', function () {
    $csp = (string) $this->get('/login')->headers->get('Content-Security-Policy');

    expect($csp)
        ->toContain("default-src 'self'")
        ->toContain("object-src 'none'")
        ->toContain("base-uri 'self'")
        ->toContain("form-action 'self'")
        ->toContain("frame-ancestors 'none'")
        ->not->toContain('*');
});

it('mengizinkan dev server Vite di CSP hanya saat berjalan', function () {
    $hot = storage_path('framework/testing/hot-uji-csp');
    Vite::useHotFile($hot);

    expect(SecurityHeaders::csp())->not->toContain('5173');

    file_put_contents($hot, 'http://[::1]:5173');

    try {
        expect(SecurityHeaders::csp())
            ->toContain("script-src 'self' 'unsafe-inline' 'unsafe-eval' http://[::1]:5173")
            ->toContain("connect-src 'self' http://[::1]:5173 ws://[::1]:5173");
    } finally {
        unlink($hot);
    }
});

it('memuat font panel Admin dari server sendiri, bukan pihak ketiga', function () {
    $this->get('/admin/login')
        ->assertOk()
        ->assertDontSee('fonts.bunny.net')
        ->assertSee('@font-face', escape: false);
});
