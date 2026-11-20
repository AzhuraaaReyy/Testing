<?php

use App\Enums\Tingkat;

it('mengurutkan Tingkat X, XI, XII sesuai urutan enum, bukan alfabet', function () {
    expect(Tingkat::X->urutan())->toBe(0)
        ->and(Tingkat::XI->urutan())->toBe(1)
        ->and(Tingkat::XII->urutan())->toBe(2);
});

it('menyediakan binding CASE pengurutan: pasangan nilai => posisi, lalu posisi ELSE', function () {
    expect(Tingkat::bindingUrutan())->toBe(['X', 0, 'XI', 1, 'XII', 2, 3]);
});
