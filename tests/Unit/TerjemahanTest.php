<?php

/*
| Setiap teks bawaan Laravel & Filament harus punya padanan Bahasa Indonesia (APP_LOCALE=id),
| baik dari paket itu sendiri maupun override di lang/. Kunci tanpa terjemahan akan tampil
| berbahasa Inggris; test ini mendeteksinya, termasuk setelah upgrade paket.
*/

/**
 * @return array<string, mixed> kunci "berkas.kunci.bertingkat" => teks
 */
function teksTerjemahan(string $dir): array
{
    if (! is_dir($dir)) {
        return [];
    }

    $hasil = [];
    $berkas = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));

    foreach ($berkas as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }

        $nama = str_replace('\\', '/', substr($file->getPathname(), strlen($dir) + 1, -4));
        $hasil += collect(include $file->getPathname())->dot()->mapWithKeys(fn ($teks, $kunci) => ["{$nama}.{$kunci}" => $teks])->all();
    }

    return $hasil;
}

function akarProyek(): string
{
    return dirname(__DIR__, 2);
}

it('menerjemahkan seluruh teks bawaan Laravel', function () {
    $en = teksTerjemahan(akarProyek().'/vendor/laravel/framework/src/Illuminate/Translation/lang/en');
    $id = teksTerjemahan(akarProyek().'/lang/id');

    // custom & attributes berisi contoh/nama field khusus aplikasi, bukan teks bawaan.
    $kurang = array_filter(
        array_keys(array_diff_key($en, $id)),
        fn (string $kunci): bool => ! str_starts_with($kunci, 'validation.custom') && $kunci !== 'validation.attributes',
    );

    expect(array_values($kurang))->toBe([]);
});

it('menyediakan halaman error Indonesia untuk setiap halaman error bawaan Laravel', function () {
    $bawaan = glob(akarProyek().'/vendor/laravel/framework/src/Illuminate/Foundation/Exceptions/views/[0-9]*.blade.php');
    $kesalahan = include akarProyek().'/lang/id/kesalahan.php';

    foreach ($bawaan as $view) {
        $kode = basename($view, '.blade.php');

        expect(akarProyek()."/resources/views/errors/{$kode}.blade.php")->toBeFile()
            ->and($kesalahan)->toHaveKey($kode);
    }
});

it('menerjemahkan seluruh teks bawaan Filament', function (string $paket, string $namespace) {
    $lang = akarProyek()."/vendor/filament/{$paket}/resources/lang";
    $id = teksTerjemahan("{$lang}/id") + teksTerjemahan(akarProyek()."/lang/vendor/{$namespace}/id");

    expect(array_keys(array_diff_key(teksTerjemahan("{$lang}/en"), $id)))->toBe([]);
})->with([
    ['actions', 'filament-actions'],
    ['filament', 'filament-panels'],
    ['forms', 'filament-forms'],
    ['infolists', 'filament-infolists'],
    ['notifications', 'filament-notifications'],
    ['schemas', 'filament-schemas'],
    ['support', 'filament'],
    ['tables', 'filament-tables'],
    ['widgets', 'filament-widgets'],
]);
