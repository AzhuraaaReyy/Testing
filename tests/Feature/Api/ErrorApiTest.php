<?php

use App\Models\User;
use App\Providers\AppServiceProvider;
use Laravel\Sanctum\Sanctum;

/*
| Error API: JSON {message} berbahasa Indonesia untuk setiap kode status.
*/

it('membalas error dengan JSON berbahasa Indonesia', function (Closure $permintaan, int $status, string $pesan) {
    $permintaan()->assertStatus($status)->assertExactJson(['message' => $pesan]);
})->with([
    '401 tanpa token' => [fn () => test()->getJson('/api/v1/me'), 401, 'Silakan login terlebih dahulu.'],
    '403 peran tidak sesuai' => [function () {
        Sanctum::actingAs(User::factory()->guru()->create());

        return test()->getJson('/api/v1/siswa');
    }, 403, 'Anda tidak memiliki akses.'],
    '404 data' => [function () {
        Sanctum::actingAs(User::factory()->create());

        return test()->getJson('/api/v1/kelas/999999');
    }, 404, 'Data tidak ditemukan.'],
    '404 endpoint' => [fn () => test()->getJson('/api/v1/tidak-ada'), 404, 'Endpoint tidak ditemukan.'],
    '405 metode' => [fn () => test()->getJson('/api/v1/login'), 405, 'Metode HTTP tidak diizinkan untuk endpoint ini.'],
]);

it('membatasi jumlah permintaan per menit (429)', function () {
    Sanctum::actingAs(User::factory()->create());

    foreach (range(1, AppServiceProvider::BATAS_API_PER_MENIT) as $_) {
        $this->getJson('/api/v1/me')->assertOk();
    }

    $this->getJson('/api/v1/me')
        ->assertTooManyRequests()
        ->assertJsonPath('message', 'Terlalu banyak permintaan. Silakan coba lagi nanti.');
});

it('tidak mengubah perilaku error halaman web', function () {
    $this->get('/admin/tidak-ada')->assertNotFound()->assertSeeText('Halaman tidak ditemukan');
});
