<?php

use Illuminate\Support\Facades\Route;

/*
| SEC-10: dengan APP_DEBUG=false (wajib di production), error tak terduga tidak membocorkan
| detail internal (pesan exception, stack trace, path file) di API maupun halaman web.
*/

beforeEach(function () {
    $gagal = fn () => throw new RuntimeException('rahasia-internal: koneksi db-prod gagal');

    Route::get('/api/uji-error', $gagal);
    Route::middleware('web')->get('/uji-error', $gagal);
});

it('menyembunyikan detail error di API saat debug mati', function () {
    config(['app.debug' => false]);

    $this->getJson('/api/uji-error')
        ->assertServerError()
        ->assertExactJson(['message' => 'Terjadi kesalahan pada server.']);
});

it('menyembunyikan detail error di halaman web saat debug mati', function () {
    config(['app.debug' => false]);

    $this->get('/uji-error')
        ->assertServerError()
        ->assertSeeText('Terjadi kesalahan')
        ->assertDontSee('rahasia-internal')
        ->assertDontSee('RuntimeException')
        ->assertDontSee(base_path(), escape: false);
});

it('menampilkan detail error hanya saat debug aktif (pengembangan)', function () {
    config(['app.debug' => true]);

    $this->getJson('/api/uji-error')
        ->assertServerError()
        ->assertJsonPath('message', 'rahasia-internal: koneksi db-prod gagal')
        ->assertJsonStructure(['exception', 'trace']);
});
