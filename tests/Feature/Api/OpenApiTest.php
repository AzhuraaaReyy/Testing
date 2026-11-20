<?php

use App\Enums\JenisKelamin;
use App\Enums\JenisLaporan;
use App\Enums\Role;
use App\Enums\Tingkat;
use App\Http\Requests\Auth\PortalLoginRequest;
use App\Http\Requests\DataMaster\AkunPortalRequest;
use App\Http\Requests\DataMaster\GuruRequest;
use App\Http\Requests\DataMaster\KelasRequest;
use App\Http\Requests\DataMaster\PulihkanGuruRequest;
use App\Http\Requests\DataMaster\PulihkanSiswaRequest;
use App\Http\Requests\DataMaster\SiswaRequest;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Routing\Route as RouteDefinition;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Sanctum;
use Symfony\Component\Yaml\Yaml;

/*
| docs/api/openapi.yaml harus selalu cocok dengan aplikasi: rute, bentuk respons (dari endpoint
| sungguhan), dan field payload (dari FormRequest). Perubahan API tanpa memperbarui spesifikasi
| akan menggagalkan test ini, sehingga Postman/ZAP selalu memakai kontrak yang benar.
*/

/**
 * @return array<string, mixed>
 */
function spesifikasi(): array
{
    static $spec = null;

    return $spec ??= Yaml::parseFile(base_path('docs/api/openapi.yaml'));
}

/**
 * @return array<string, mixed>
 */
function skema(string $nama): array
{
    return spesifikasi()['components']['schemas'][$nama];
}

it('adalah dokumen OpenAPI 3.0.3 yang valid dengan semua $ref terselesaikan', function () {
    $spec = spesifikasi();
    preg_match_all('/"\$ref":"#\/([^"]+)"/', json_encode($spec, JSON_UNESCAPED_SLASHES), $refs);

    expect($spec['openapi'])->toBe('3.0.3');

    foreach (array_unique($refs[1]) as $ref) {
        expect(data_get($spec, str_replace('/', '.', $ref)))->not->toBeNull("\$ref tidak ditemukan: #/{$ref}");
    }
});

it('mendokumentasikan tepat semua rute API (tidak kurang, tidak lebih)', function () {
    $rute = collect(Route::getRoutes()->getRoutes())
        ->filter(fn (RouteDefinition $route): bool => str_starts_with($route->uri(), 'api/v1/'))
        ->flatMap(fn (RouteDefinition $route): array => collect($route->methods())
            ->reject(fn (string $metode): bool => in_array($metode, ['HEAD', 'PATCH'], true)) // PATCH = alias PUT
            ->map(fn (string $metode): string => strtolower($metode).' '.substr($route->uri(), strlen('api/v1')))
            ->all())
        ->sort()->values()->all();

    $dokumen = collect(spesifikasi()['paths'])
        ->flatMap(fn (array $operasi, string $path): array => collect(array_keys($operasi))
            ->reject(fn (string $kunci): bool => $kunci === 'parameters')
            ->map(fn (string $metode): string => "{$metode} {$path}")
            ->all())
        ->sort()->values()->all();

    expect($dokumen)->toBe($rute);
});

it('mendeskripsikan field payload persis seperti aturan FormRequest', function (string $skema, string $request) {
    $aturan = collect((new $request)->rules())->reject(fn ($_, string $field): bool => str_contains($field, '.'));
    $wajib = $aturan->filter(fn (array $daftar): bool => in_array('required', $daftar, true))->keys()->sort()->values()->all();

    expect(array_keys(skema($skema)['properties']))->toEqualCanonicalizing($aturan->keys()->all())
        ->and(collect(skema($skema)['required'] ?? [])->sort()->values()->all())->toBe($wajib);
})->with([
    ['LoginPayload', PortalLoginRequest::class],
    ['KelasPayload', KelasRequest::class],
    ['SiswaPayload', SiswaRequest::class],
    ['GuruPayload', GuruRequest::class],
    ['AkunPortalPayload', AkunPortalRequest::class],
    ['PulihkanSiswaPayload', PulihkanSiswaRequest::class],
    ['PulihkanGuruPayload', PulihkanGuruRequest::class],
]);

it('mendeskripsikan bentuk respons persis seperti JSON yang dikirim endpoint', function () {
    $kelas = Kelas::factory()->create();
    $siswa = User::factory()->siswa(Siswa::factory()->for($kelas)->create())->create()->siswa;
    $guru = Guru::factory()->hasAttached($kelas)->create();
    $admin = User::factory()->create();
    Sanctum::actingAs($admin);

    $respons = [
        'User' => $this->getJson('/api/v1/me')->json('data'),
        'Ringkasan' => $this->getJson('/api/v1/ringkasan')->json('data'),
        'LaporanKelas' => $this->getJson('/api/v1/laporan/siswa-kelas-guru')->json('data.0'),
        'Kelas' => $this->getJson("/api/v1/kelas/{$kelas->id}")->json('data'),
        'Siswa' => $this->getJson("/api/v1/siswa/{$siswa->id}")->json('data'),
        'Guru' => $this->getJson("/api/v1/guru/{$guru->id}")->json('data'),
        'KelasRingkas' => $this->getJson("/api/v1/siswa/{$siswa->id}")->json('data.kelas'),
    ];

    foreach ($respons as $nama => $json) {
        expect(array_keys(skema($nama)['properties']))->toEqualCanonicalizing(array_keys($json), "Skema {$nama} tidak cocok dengan respons");
    }

    // Login: struktur pembungkus token.
    $login = $this->postJson('/api/v1/login', ['email' => $admin->email, 'password' => 'password'])->json();
    expect(array_keys(skema('LoginRespons')['properties']))->toEqualCanonicalizing(array_keys($login));
});

it('menyamakan nilai enum di spesifikasi dengan enum PHP', function (string $skema, string $enum) {
    expect(skema($skema)['enum'])->toBe(array_column($enum::cases(), 'value'));
})->with([
    ['Role', Role::class],
    ['Tingkat', Tingkat::class],
    ['JenisKelamin', JenisKelamin::class],
    ['JenisLaporan', JenisLaporan::class],
]);
