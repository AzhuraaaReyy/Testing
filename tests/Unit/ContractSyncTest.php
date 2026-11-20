<?php

use App\DTOs\Dashboard\RingkasanSekolahData;
use App\DTOs\Laporan\KelasRingkasanData;
use App\DTOs\Navigation\NavGroupData;
use App\DTOs\Navigation\NavItemData;
use App\Enums\Icon;
use App\Enums\JenisKelamin;
use App\Enums\JenisLaporan;
use App\Enums\Role;
use App\Enums\Tingkat;
use App\Http\Requests\Admin\BuatAdminRequest;
use App\Http\Requests\Auth\PortalLoginRequest;
use App\Http\Requests\DataMaster\AkunPortalRequest;
use App\Http\Requests\DataMaster\DaftarDataMasterRequest;
use App\Http\Requests\DataMaster\GuruRequest;
use App\Http\Requests\DataMaster\KelasRequest;
use App\Http\Requests\DataMaster\PulihkanGuruRequest;
use App\Http\Requests\DataMaster\PulihkanSiswaRequest;
use App\Http\Requests\DataMaster\SiswaRequest;
use App\Http\Requests\LaporanRequest;
use Illuminate\Foundation\Http\FormRequest;

/*
| Menjaga agar resources/js/types/contracts.ts selalu identik dengan kontrak PHP.
| Jika salah satu sisi berubah tanpa sisi lainnya, test ini gagal.
*/

function contractsSource(): string
{
    return file_get_contents(dirname(__DIR__, 2).'/resources/js/types/contracts.ts');
}

/**
 * @return list<string>
 */
function tsConstArray(string $name): array
{
    preg_match('/export const '.$name.' = \[(.*?)\] as const;/s', contractsSource(), $match);
    expect($match)->not->toBeEmpty("Konstanta {$name} tidak ditemukan di contracts.ts");

    preg_match_all("/'([^']*)'/", $match[1], $values);

    return $values[1];
}

/**
 * @return array<string, string>
 */
function tsConstObject(string $name): array
{
    preg_match('/export const '.$name.' = \{(.*?)\} as const;/s', contractsSource(), $match);
    expect($match)->not->toBeEmpty("Konstanta {$name} tidak ditemukan di contracts.ts");

    preg_match_all("/'([^']+)':\s*'([^']*)'/", $match[1], $pairs, PREG_SET_ORDER);

    return collect($pairs)->mapWithKeys(fn (array $p): array => [$p[1] => $p[2]])->all();
}

/**
 * @return array<string, string> nama properti => tipe TypeScript
 */
function tsInterface(string $name): array
{
    preg_match('/export interface '.$name.'(?: extends \w+)? \{(.*?)\n\}/s', contractsSource(), $match);
    expect($match)->not->toBeEmpty("Interface {$name} tidak ditemukan di contracts.ts");

    preg_match_all('/^\s*(\w+)\??:\s*([^;]+);/m', $match[1], $props, PREG_SET_ORDER);

    return collect($props)->mapWithKeys(fn (array $p): array => [$p[1] => trim($p[2])])->all();
}

/**
 * Properti constructor DTO PHP dipetakan ke tipe TypeScript yang setara.
 *
 * @param  class-string  $class
 * @return array<string, string>
 */
function phpDtoAsTs(string $class): array
{
    $constructor = (new ReflectionClass($class))->getConstructor();

    return collect($constructor->getParameters())
        ->mapWithKeys(fn (ReflectionParameter $param): array => [$param->getName() => phpTypeAsTs($param, (string) $constructor->getDocComment())])
        ->all();
}

/**
 * Tipe skalar → tipe TS; enum → nama pendeknya (cerminannya bernama sama di contracts.ts);
 * array → tipe elemen dari PHPDoc `@param list<T> $nama`.
 */
function phpTypeAsTs(ReflectionParameter $param, string $docComment): string
{
    $type = $param->getType();
    $name = $type instanceof ReflectionNamedType ? $type->getName() : (string) $type;

    $ts = match (true) {
        in_array($name, ['int', 'float'], true) => 'number',
        $name === 'string' => 'string',
        $name === 'bool' => 'boolean',
        $name === 'array' => listElementAsTs($param->getName(), $docComment).'[]',
        enum_exists($name) => class_basename($name),
        default => throw new RuntimeException("Tipe PHP belum dipetakan: {$type}"),
    };

    return $type?->allowsNull() ? "{$ts} | null" : $ts;
}

function listElementAsTs(string $param, string $docComment): string
{
    preg_match('/@param\s+list<([\w\\\\]+)>\s+\$'.$param.'\b/', $docComment, $match);
    expect($match)->not->toBeEmpty("PHPDoc @param list<T> \${$param} wajib ada agar tipe elemen dapat dicerminkan");

    return $match[1] === 'string' ? 'string' : class_basename($match[1]);
}

it('menyamakan nilai enum', function (string $tsName, string $enum) {
    expect(tsConstArray($tsName))->toBe(array_column($enum::cases(), 'value'));
})->with([
    ['JENIS_LAPORAN', JenisLaporan::class],
    ['TINGKAT', Tingkat::class],
    ['ROLE', Role::class],
    ['JENIS_KELAMIN', JenisKelamin::class],
    ['ICON', Icon::class],
]);

it('mencerminkan setiap enum dan DTO aplikasi', function () {
    $source = contractsSource();
    $kelas = collect([...glob(dirname(__DIR__, 2).'/app/Enums/*.php'), ...glob(dirname(__DIR__, 2).'/app/DTOs/*/*.php')])
        ->map(fn (string $path): string => 'App\\'.str_replace(['/', '.php'], ['\\', ''], substr($path, strlen(dirname(__DIR__, 2).'/app/'))));

    expect($kelas->reject(fn (string $class): bool => str_contains($source, "Cermin {$class}"))->values()->all())->toBe([]);
});

it('menyamakan field dan tipe DTO', function (string $tsName, string $dto) {
    expect(tsInterface($tsName))->toBe(phpDtoAsTs($dto));
})->with([
    ['KelasRingkasanData', KelasRingkasanData::class],
    ['RingkasanSekolahData', RingkasanSekolahData::class],
    ['NavItemData', NavItemData::class],
    ['NavGroupData', NavGroupData::class],
]);

it('menyamakan field payload dengan rules FormRequest', function (string $tsName, string $request) {
    // Aturan per elemen ("kelas.*") bukan field payload tersendiri.
    $fields = array_values(array_filter(array_keys((new $request)->rules()), fn (string $key): bool => ! str_contains($key, '.')));

    expect(array_keys(tsInterface($tsName)))->toBe($fields);
})->with([
    ['LaporanQuery', LaporanRequest::class],
    ['PortalLoginPayload', PortalLoginRequest::class],
    ['BuatAdminPayload', BuatAdminRequest::class],
    ['KelasPayload', KelasRequest::class],
    ['SiswaPayload', SiswaRequest::class],
    ['GuruPayload', GuruRequest::class],
    ['AkunPortalPayload', AkunPortalRequest::class],
    ['DaftarDataMasterQuery', DaftarDataMasterRequest::class],
    ['PulihkanSiswaPayload', PulihkanSiswaRequest::class],
    ['PulihkanGuruPayload', PulihkanGuruRequest::class],
]);

it('mencerminkan setiap FormRequest aplikasi', function () {
    $source = contractsSource();
    $base = dirname(__DIR__, 2).'/app/Http/Requests';
    $requests = collect([...glob("{$base}/*.php"), ...glob("{$base}/*/*.php")])
        ->map(fn (string $path): string => 'App\\'.str_replace(['/', '.php'], ['\\', ''], substr($path, strlen(dirname(__DIR__, 2).'/app/'))))
        // Hanya kelas FormRequest (bukan trait/helper di folder Concerns).
        ->filter(fn (string $class): bool => is_subclass_of($class, FormRequest::class));

    expect($requests)->not->toBeEmpty()
        ->and($requests->reject(fn (string $class): bool => str_contains($source, "Cermin rules() {$class}"))->values()->all())->toBe([]);
});

it('menyamakan pesan validasi login sisi klien dengan server', function () {
    expect(tsConstObject('PORTAL_LOGIN_MESSAGES'))->toBe((new PortalLoginRequest)->messages());
});
