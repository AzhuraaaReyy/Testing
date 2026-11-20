<?php

use App\Enums\JenisLaporan;
use App\Filament\Pages\Laporan;
use App\Models\Kelas;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

/*
| Instruksi testing poin 5–7: list dalam 1 tabel, tanpa filter, dan Kelas tidak boleh muncul
| lebih dari sekali. Diuji dengan data seeder lengkap (Guru di banyak Kelas, Kelas tanpa
| Siswa/Guru, data di Trash) dan menelusuri SELURUH halaman laporan.
*/

/**
 * Nama Kelas pada kolom Kelas setiap baris, dari seluruh halaman laporan.
 *
 * @return array{kelas: list<string>, tabelPerHalaman: list<int>, kontrolFilter: int}
 */
function telusuriLaporan(JenisLaporan $jenis): array
{
    $kelas = [];
    $tabelPerHalaman = [];
    $kontrolFilter = 0;
    $halaman = 1;

    do {
        $html = test()->get(Laporan::getUrl(['jenis' => $jenis, 'page' => $halaman]))->assertOk()->getContent();

        $tabelPerHalaman[] = substr_count($html, '<table');
        $kontrolFilter += preg_match_all('/fi-ta-filters|fi-ta-search|fi-global-search|type="search"|<select/i', $html);

        preg_match('/<tbody[\s\S]*?<\/tbody>/', $html, $tbody);
        preg_match_all('/<tr[\s\S]*?<\/tr>/', $tbody[0], $baris);

        foreach ($baris[0] as $tr) {
            preg_match_all('/<td[^>]*>([\s\S]*?)<\/td>/', $tr, $sel);
            $kelas[] = trim(strip_tags($sel[1][1])); // Kolom ke-2 = Kelas (kolom ke-1 = No.)
        }

        $halaman++;
    } while (str_contains($html, 'nextPage(') && $halaman <= 20);

    return ['kelas' => $kelas, 'tabelPerHalaman' => $tabelPerHalaman, 'kontrolFilter' => $kontrolFilter];
}

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
    $this->actingAs(User::query()->where('email', 'admin@sekolah.test')->sole());
});

it('menampilkan list dalam 1 tabel tanpa filter, setiap Kelas aktif tepat sekali', function (JenisLaporan $jenis) {
    $hasil = telusuriLaporan($jenis);
    $kelasAktif = Kelas::query()->pluck('nama_kelas')->sort()->values()->all();
    $tampil = collect($hasil['kelas']);

    expect($hasil['tabelPerHalaman'])->each->toBe(1)
        ->and($hasil['kontrolFilter'])->toBe(0)
        ->and($tampil->duplicates()->all())->toBe([])
        ->and($tampil->sort()->values()->all())->toBe($kelasAktif)
        ->and($tampil->all())->not->toContain('X Arsip'); // Kelas di Trash
})->with(JenisLaporan::cases());
