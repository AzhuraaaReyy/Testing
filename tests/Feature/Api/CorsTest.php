<?php

use Illuminate\Testing\TestResponse;

/*
| SEC2-05: API hanya boleh dipanggil JavaScript dari origin aplikasi sendiri (APP_URL).
*/

function preflight(string $origin): TestResponse
{
    return test()->call('OPTIONS', '/api/v1/me', server: [
        'HTTP_ORIGIN' => $origin,
        'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'GET',
        'HTTP_ACCESS_CONTROL_REQUEST_HEADERS' => 'authorization',
    ]);
}

function originAplikasi(): string
{
    return rtrim((string) config('app.url'), '/');
}

it('menolak origin situs lain', function () {
    // Dengan satu origin yang diizinkan, header selalu berisi origin itu (bukan origin peminta
    // atau "*"), sehingga browser memblokir respons untuk situs lain.
    foreach ([preflight('https://situs-lain.example'), $this->getJson('/api/v1/me', ['Origin' => 'https://situs-lain.example'])] as $response) {
        expect($response->headers->get('Access-Control-Allow-Origin'))
            ->not->toBe('https://situs-lain.example')
            ->not->toBe('*');
    }
});

it('mengizinkan origin aplikasi sendiri', function () {
    preflight(originAplikasi())->assertHeader('Access-Control-Allow-Origin', originAplikasi());
});

it('tidak lagi memakai wildcard dan tidak mengirim kredensial cookie', function () {
    expect(config('cors.allowed_origins'))->not->toContain('*')->not->toBeEmpty()
        ->and(config('cors.supports_credentials'))->toBeFalse();
});
