<?php

namespace App\Http;

use Illuminate\Http\Request;

/**
 * Kontrak render parsial: klien meminta satu fragmen Blade (mis. hanya tabel) lewat header.
 *
 *   Request : GET <url> + header `X-Fragment: <nama fragmen>`
 *   Sukses  : 200 text/html berisi fragmen saja
 *   Gagal   : JSON standar Laravel — 422 {message, errors}, 500 {message} (lihat bootstrap/app.php)
 *
 * Nilai dirender ke atribut data-* sehingga TypeScript memakai sumber yang sama.
 * Cermin TypeScript: resources/js/types/contracts.ts → FragmentRequest.
 */
final class PartialResponse
{
    public const HEADER = 'X-Fragment';

    public const TABEL = 'tabel';

    public static function requested(Request $request, string $fragment): bool
    {
        return $request->header(self::HEADER) === $fragment;
    }

    public static function isPartial(Request $request): bool
    {
        return $request->hasHeader(self::HEADER);
    }
}
