<?php

use App\Filament\Resources\Guru\Pages\ListGuru;
use App\Filament\Resources\Kelas\Pages\ListKelas;
use App\Filament\Resources\Siswa\Pages\ListSiswa;
use App\Filament\Widgets\DistribusiKelasWidget;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Livewire\Livewire;

/*
| Temuan audit PRD yang berasal dari perilaku bawaan Filament. Tiap test menjaga satu aturan PRD.
*/

beforeEach(fn () => $this->actingAs(User::factory()->create()));

/**
 * Pasangan "No." => nama per baris, dibaca dari HTML tabel Filament yang dirender.
 *
 * @return array<string, string>
 */
function nomorDanNama(string $html, string $kolomNama = 'nama'): array
{
    // Filament menamai sel dengan kebab-case: nama_kelas → fi-ta-cell-nama-kelas.
    $sel = fn (string $kolom): array => preg_match_all('/fi-ta-cell-'.preg_quote(str($kolom)->camel()->kebab()->toString(), '/').'[\s"][\s\S]*?fi-ta-text-item[^>]*>\s*([^<]+?)\s*</', $html, $m) ? $m[1] : [];

    return array_combine($sel('nomor'), $sel($kolomNama));
}

it('tidak menyediakan pencarian di panel Admin (PRD 2.4 poin 5–6)', function (string $url) {
    $this->get($url)
        ->assertOk()
        ->assertDontSee('fi-global-search', escape: false)
        ->assertDontSee('fi-ta-search-field', escape: false);
})->with(['/admin', '/admin/siswa', '/admin/kelas', '/admin/guru']);

it('memberi judul tabel CRUD yang terpisah dari header kolom (PRD 5.3, 15.1)', function (string $halaman, string $label) {
    Livewire::test($halaman)
        ->assertSeeHtml('fi-ta-header-heading')
        ->assertSeeText("Daftar {$label}")
        ->set('activeTab', 'trash')
        ->assertSeeText("Trash {$label}");
})->with([
    [ListSiswa::class, 'Siswa'],
    [ListKelas::class, 'Kelas'],
    [ListGuru::class, 'Guru'],
]);

it('memformat angka dengan pemisah ribuan titik (PRD 18.2)', function () {
    Siswa::factory()->count(1050)->for(Kelas::factory())->create();

    Livewire::test(ListSiswa::class)
        ->assertSeeText('dari 1.050 data')
        ->assertDontSeeText('1,050');
});

describe('pagination tabel Admin (PRD 15.2–15.4)', function () {
    beforeEach(fn () => Siswa::factory()->count(25)->for(Kelas::factory())->create());

    it('menampilkan Sebelumnya redup di halaman pertama dan Berikutnya aktif', function () {
        $html = Livewire::test(ListSiswa::class)->html();

        // Nonaktif = <span aria-disabled>, tetap terlihat (bukan disembunyikan).
        expect($html)->toMatch('/<span[^>]*aria-disabled="true"[^>]*>[\s\S]*?Sebelumnya/')
            ->and($html)->toMatch('/<a[^>]*wire:click\.prevent="nextPage\(&#039;page&#039;\)"[^>]*>[\s\S]*?Berikutnya/')
            ->and($html)->not->toContain('Selanjutnya');
    });

    it('menampilkan Berikutnya redup di halaman terakhir', function () {
        $html = Livewire::test(ListSiswa::class)->call('gotoPage', 3)->html();

        expect($html)->toMatch('/<span[^>]*aria-disabled="true"[^>]*>[\s\S]*?Berikutnya/')
            ->and($html)->toContain('Menampilkan 21–25 dari 25 data');
    });

    it('berpindah halaman lewat Livewire tanpa memuat ulang halaman', function () {
        Livewire::test(ListSiswa::class)
            ->assertSeeHtml('wire:click.prevent="gotoPage(2, &#039;page&#039;)"')
            ->call('gotoPage', 2)
            ->assertSet('paginators.page', 2)
            ->assertSeeText('Menampilkan 11–20 dari 25 data');
    });

    it('tidak menyembunyikan ringkasan & nomor halaman di layar kecil', function () {
        $html = Livewire::test(ListSiswa::class)->html();

        // Kelas bawaan Filament yang menyembunyikan elemen di mobile tidak lagi dipakai.
        expect($html)->not->toContain('fi-pagination-overview')
            ->and($html)->not->toContain('fi-pagination-items')
            ->and($html)->toContain('Menampilkan 1–10 dari 25 data');
    });

    it('menandai pagination agar ikut disembunyikan saat error state tabel (PRD 16.3)', function () {
        $css = file_get_contents(resource_path('css/filament/admin/theme.css'));

        Livewire::test(ListSiswa::class)->assertSeeHtml('data-table-pagination');

        expect($css)->toContain("[data-table-state='error'] :is([data-table-area], .fi-ta-content-ctn, [data-table-pagination])");
    });
});

describe('kolom No.', function () {
    it('ada sebagai kolom pertama di setiap tabel CRUD', function (string $halaman, Closure $buat) {
        $record = $buat();
        $lw = Livewire::test($halaman);

        expect(array_key_first($lw->instance()->getTable()->getColumns()))->toBe('nomor');

        $lw->assertTableColumnStateSet('nomor', '1', $record)->assertSeeText('No.');
    })->with([
        [ListSiswa::class, fn () => Siswa::factory()->create()],
        [ListKelas::class, fn () => Kelas::factory()->create()],
        [ListGuru::class, fn () => Guru::factory()->create()],
    ]);

    // Diperiksa dari HTML hasil render: assertTableColumnStateSet() Filament mengevaluasi
    // rowIndex dengan konteks baris terakhir, bukan baris record yang diminta.
    it('berlanjut antar-halaman pagination: halaman 2 dimulai dari 11', function () {
        Siswa::factory()->count(12)->for(Kelas::factory())->create();
        $nama = Siswa::query()->orderBy('nama')->pluck('nama')->all();

        $halaman1 = nomorDanNama(Livewire::test(ListSiswa::class)->html());
        $halaman2 = nomorDanNama(Livewire::test(ListSiswa::class)->call('gotoPage', 2)->html());

        expect($halaman1)->toBe(array_combine(array_map('strval', range(1, 10)), array_slice($nama, 0, 10)))
            ->and($halaman2)->toBe(['11' => $nama[10], '12' => $nama[11]]);
    });

    it('ada di tabel distribusi Dashboard', function (string $relasi) {
        Kelas::factory()->count(2)->create();
        $nama = Kelas::query()->urutanTampilan()->pluck('nama_kelas')->all();

        $html = Livewire::test(DistribusiKelasWidget::class, ['relasi' => $relasi])->html();

        expect(nomorDanNama($html, 'nama_kelas'))->toBe(['1' => $nama[0], '2' => $nama[1]]);
    })->with(['siswa', 'guru']);
});

it('tetap menampilkan judul tabel saat tabel kosong (PRD 14.9)', function () {
    Livewire::test(ListSiswa::class)
        ->assertSeeText(['Daftar Siswa', 'Belum ada data Siswa.']);
});
