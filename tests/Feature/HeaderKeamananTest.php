<?php

use App\Filament\AvatarProviders\InisialAvatarProvider;
use App\Http\Middleware\PreventRequestForgeryTanpaCookie;
use App\Http\Middleware\SecurityHeaders;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Vite;
use Illuminate\Testing\TestResponse;

/*
| SEC-03: header keamanan dikirim di semua pintu, termasuk respons error API.
*/

function assertHeaderKeamanan(TestResponse $response, string $area): TestResponse
{
    foreach (SecurityHeaders::HEADERS as $nama => $nilai) {
        $response->assertHeader($nama, $nilai);
    }

    $csp = (string) $response->headers->get('Content-Security-Policy');
    $nonce = preg_match("/'nonce-([^']+)'/", $csp, $cocok) ? $cocok[1] : null;

    expect($csp)->toBe(SecurityHeaders::csp($area, $nonce));

    if ($area === SecurityHeaders::AREA_PORTAL) {
        expect($nonce)->not->toBeEmpty();
    }

    return $response;
}

it('mengirim header keamanan di halaman login dan halaman terlindungi', function (string $url, string $area) {
    assertHeaderKeamanan($this->get($url), $area);
})->with([
    'login portal' => ['/login', SecurityHeaders::AREA_PORTAL],
    'login Admin' => ['/admin/login', SecurityHeaders::AREA_ADMIN],
    'redirect tamu' => ['/portal', SecurityHeaders::AREA_PORTAL],
]);

it('mengirim header keamanan di panel Admin dan portal setelah login', function () {
    assertHeaderKeamanan($this->actingAs(User::factory()->create())->get('/admin'), SecurityHeaders::AREA_ADMIN)->assertOk();
    assertHeaderKeamanan($this->actingAs(User::factory()->siswa()->create())->get(route('portal.dashboard')), SecurityHeaders::AREA_PORTAL)->assertOk();
});

it('mengirim header keamanan di respons API, termasuk respons error', function () {
    assertHeaderKeamanan($this->getJson('/api/v1/tidak-ada'), SecurityHeaders::AREA_API)->assertNotFound();
    assertHeaderKeamanan($this->postJson('/api/v1/login', []), SecurityHeaders::AREA_API)->assertUnprocessable();
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

    expect(SecurityHeaders::csp(SecurityHeaders::AREA_ADMIN))->not->toContain('5173')
        ->and(SecurityHeaders::csp(SecurityHeaders::AREA_PORTAL, 'n1'))->not->toContain('5173');

    file_put_contents($hot, 'http://[::1]:5173');

    try {
        expect(SecurityHeaders::csp(SecurityHeaders::AREA_ADMIN))
            ->toContain("script-src 'self' 'unsafe-inline' 'unsafe-eval' http://[::1]:5173")
            ->toContain("connect-src 'self' http://[::1]:5173 ws://[::1]:5173");

        // HMR Vite menyuntikkan <style> tanpa nonce: hanya selama dev server berjalan.
        expect(SecurityHeaders::csp(SecurityHeaders::AREA_PORTAL, 'n1'))
            ->toContain("script-src 'self' 'nonce-n1' http://[::1]:5173")
            ->toContain("style-src 'self' 'unsafe-inline' http://[::1]:5173")
            ->and(SecurityHeaders::csp(SecurityHeaders::AREA_API))->not->toContain('5173');
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

it('memakai CSP ketat bernonce di portal, tanpa unsafe-inline/unsafe-eval (SEC2-02)', function (string $url, bool $login) {
    if ($login) {
        $this->actingAs(User::factory()->siswa()->create());
    }

    $response = $this->get($url);
    $csp = (string) $response->headers->get('Content-Security-Policy');
    preg_match("/'nonce-([^']+)'/", $csp, $cocok);

    expect($csp)->not->toContain('unsafe-inline')->not->toContain('unsafe-eval')
        ->and($cocok[1] ?? '')->toHaveLength(40);

    // Setiap <script> dan <style> di halaman membawa nonce dari header yang sama.
    preg_match_all('/<(?:script|style)(?:\s[^>]*)?>/i', (string) $response->getContent(), $tag);
    expect($tag[0])->not->toBeEmpty();

    foreach ($tag[0] as $html) {
        expect($html)->toContain('nonce="'.$cocok[1].'"');
    }
})->with([
    'login portal' => ['/login', false],
    'dashboard portal' => ['/portal', true],
    'laporan portal' => ['/portal/laporan/siswa-per-kelas', true],
    'halaman error 404' => ['/halaman-yang-tidak-ada', false],
]);

it('memakai nonce baru di setiap request', function () {
    $pertama = $this->get('/login')->headers->get('Content-Security-Policy');
    $kedua = $this->get('/login')->headers->get('Content-Security-Policy');

    expect($pertama)->not->toBe($kedua);
});

it('tidak memuat sumber daya apa pun di respons API', function () {
    expect($this->getJson('/api/v1/me')->headers->get('Content-Security-Policy'))
        ->toBe("default-src 'none'; base-uri 'none'; form-action 'none'; frame-ancestors 'none'");
});

it('memakai CSP longgar hanya untuk halaman error debug di lingkungan pengembangan', function (bool $debug, string $area) {
    config(['app.debug' => $debug]);
    Route::middleware('web')->get('/uji-error-csp', fn () => throw new RuntimeException('uji'));

    $csp = (string) $this->get('/uji-error-csp')->assertServerError()->headers->get('Content-Security-Policy');
    $nonce = preg_match("/'nonce-([^']+)'/", $csp, $cocok) ? $cocok[1] : null;

    expect($csp)->toBe(SecurityHeaders::csp($area, $nonce));
})->with([
    'debug (lokal)' => [true, SecurityHeaders::AREA_ADMIN],
    'production' => [false, SecurityHeaders::AREA_PORTAL],
]);

it('tidak mengirim cookie XSRF-TOKEN yang dapat dibaca JavaScript (SEC2-03)', function (string $url) {
    $this->get($url)->assertOk()->assertCookieMissing('XSRF-TOKEN');
})->with(['/login', '/admin/login']);

it('tetap memakai middleware CSRF Laravel lewat binding container', function () {
    expect(app(PreventRequestForgery::class))
        ->toBeInstanceOf(PreventRequestForgeryTanpaCookie::class);
});

it('membuat avatar akun Admin secara lokal, tanpa layanan pihak ketiga (SEC2-08)', function () {
    $admin = User::factory()->create(['name' => 'Admin Sekolah']);

    $response = $this->actingAs($admin)->get('/admin')->assertOk()->assertDontSee('ui-avatars.com');

    preg_match('/src="(data:image\/svg\+xml;base64,[^"]+)"/', (string) $response->getContent(), $cocok);
    $svg = base64_decode(substr($cocok[1] ?? '', strlen('data:image/svg+xml;base64,')));

    expect($svg)->toContain('>AS</text>')
        ->and(SecurityHeaders::csp(SecurityHeaders::AREA_ADMIN))->not->toContain('ui-avatars.com');
});

it('meng-escape inisial di dalam SVG avatar', function () {
    $user = User::factory()->make(['name' => '<script> &x']);

    $svg = base64_decode(substr((new InisialAvatarProvider)->get($user), strlen('data:image/svg+xml;base64,')));

    expect($svg)->toContain('&lt;&amp;</text>')->not->toContain('<script');
});
