<?php

use App\View\Components\Molecules\Pagination;

it('menyusun jendela nomor halaman dengan ellipsis', function (int $aktif, int $terakhir, array $harapan) {
    expect(Pagination::jendela($aktif, $terakhir))->toBe($harapan);
})->with([
    'satu halaman' => [1, 1, [1]],
    'sedikit halaman tanpa ellipsis' => [1, 4, [1, 2, 3, 4]],
    'aktif di awal' => [2, 20, [1, 2, 3, null, 20]],
    'aktif di tengah' => [10, 20, [1, null, 9, 10, 11, null, 20]],
    'aktif di akhir' => [20, 20, [1, null, 19, 20]],
    'celah satu halaman diisi nomor' => [4, 20, [1, 2, 3, 4, 5, null, 20]],
]);
