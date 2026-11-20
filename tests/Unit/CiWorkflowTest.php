<?php

/*
| SEC-09: setiap Action di workflow GitHub dikunci ke SHA commit lengkap (40 karakter heksadesimal)
| beserta komentar versinya, bukan ke tag/branch yang bisa digeser.
*/

it('mengunci setiap Action GitHub ke SHA commit', function () {
    $workflows = glob(dirname(__DIR__, 2).'/.github/workflows/*.y*ml') ?: [];
    expect($workflows)->not->toBeEmpty();

    $tidakTerkunci = [];

    foreach ($workflows as $file) {
        preg_match_all('/^\s*-?\s*uses:\s*(\S+)(.*)$/m', (string) file_get_contents($file), $cocok, PREG_SET_ORDER);
        expect($cocok)->not->toBeEmpty();

        foreach ($cocok as [, $action, $sisa]) {
            // Action lokal (./...) dan image docker:// tidak memakai ref Git.
            if (str_starts_with($action, './') || str_starts_with($action, 'docker://')) {
                continue;
            }

            if (! preg_match('/@[0-9a-f]{40}$/', $action) || ! preg_match('/^\s*#\s*v?\d+(\.\d+)*/', $sisa)) {
                $tidakTerkunci[] = basename($file).": {$action}{$sisa}";
            }
        }
    }

    expect($tidakTerkunci)->toBe([]);
});
