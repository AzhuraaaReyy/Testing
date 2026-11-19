<?php

use App\DTOs\Dashboard\RingkasanSekolahData;
use App\DTOs\Laporan\KelasRingkasanData;
use App\Enums\JenisLaporan;
use App\Enums\Role;
use App\Enums\Tingkat;
use App\Http\Requests\Auth\PortalLoginRequest;
use App\Http\Requests\LaporanRequest;

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
    return collect((new ReflectionClass($class))->getConstructor()->getParameters())
        ->mapWithKeys(fn (ReflectionParameter $param): array => [$param->getName() => match ((string) $param->getType()) {
            'int', 'float' => 'number',
            'string' => 'string',
            '?string' => 'string | null',
            'bool' => 'boolean',
            'array' => 'string[]', // list<string> sesuai PHPDoc DTO
            Tingkat::class => 'Tingkat',
            default => throw new RuntimeException("Tipe PHP belum dipetakan: {$param->getType()}"),
        }])
        ->all();
}

it('menyamakan nilai enum', function (string $tsName, string $enum) {
    expect(tsConstArray($tsName))->toBe(array_column($enum::cases(), 'value'));
})->with([
    ['JENIS_LAPORAN', JenisLaporan::class],
    ['TINGKAT', Tingkat::class],
    ['ROLE', Role::class],
]);

it('menyamakan field dan tipe DTO', function (string $tsName, string $dto) {
    expect(tsInterface($tsName))->toBe(phpDtoAsTs($dto));
})->with([
    ['KelasRingkasanData', KelasRingkasanData::class],
    ['RingkasanSekolahData', RingkasanSekolahData::class],
]);

it('menyamakan field payload dengan rules FormRequest', function (string $tsName, string $request) {
    expect(array_keys(tsInterface($tsName)))->toBe(array_keys((new $request)->rules()));
})->with([
    ['LaporanQuery', LaporanRequest::class],
    ['PortalLoginPayload', PortalLoginRequest::class],
]);

it('menyamakan pesan validasi login sisi klien dengan server', function () {
    expect(tsConstObject('PORTAL_LOGIN_MESSAGES'))->toBe((new PortalLoginRequest)->messages());
});
