<?php

/*
| Aturan arsitektur: validasi hanya di app/Http/Requests. Form Filament mengambil aturannya lewat
| App\Filament\Forms\AturanFormRequest, sehingga method validasi Filament tidak dipakai langsung.
*/

it('tidak menulis aturan validasi langsung di kode Filament', function () {
    $base = dirname(__DIR__, 2).'/app/Filament';
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS));
    $pola = '/->(required|unique|scopedUnique|exists|email|tel|regex|maxLength|minLength|length|rule|requiredWith|in|notIn)\(/';
    $pelanggaran = [];

    foreach ($files as $file) {
        // Satu-satunya penghubung yang sah ke FormRequest.
        if ($file->getExtension() !== 'php' || $file->getFilename() === 'AturanFormRequest.php') {
            continue;
        }

        foreach (file($file->getPathname()) as $nomor => $baris) {
            if (preg_match($pola, $baris)) {
                $pelanggaran[] = substr($file->getPathname(), strlen($base) + 1).':'.($nomor + 1).'  '.trim($baris);
            }
        }
    }

    expect($pelanggaran)->toBe([]);
});
