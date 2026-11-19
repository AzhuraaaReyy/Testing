<?php

use App\Enums\Tingkat;

it('mengurutkan Tingkat X, XI, XII sesuai urutan enum, bukan alfabet', function () {
    expect(Tingkat::X->urutan())->toBe(0)
        ->and(Tingkat::XI->urutan())->toBe(1)
        ->and(Tingkat::XII->urutan())->toBe(2)
        ->and(Tingkat::orderBySql('tingkat'))
        ->toBe("CASE tingkat WHEN 'X' THEN 0 WHEN 'XI' THEN 1 WHEN 'XII' THEN 2 ELSE 3 END");
});
