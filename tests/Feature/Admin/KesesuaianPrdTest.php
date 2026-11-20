<?php

use App\Filament\Resources\Guru\Pages\ListGuru;
use App\Filament\Resources\Kelas\Pages\ListKelas;
use App\Filament\Resources\Siswa\Pages\ListSiswa;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Livewire\Livewire;

/*
| Temuan audit PRD yang berasal dari perilaku bawaan Filament. Tiap test menjaga satu aturan PRD.
*/

beforeEach(fn () => $this->actingAs(User::factory()->create()));

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

it('tetap menampilkan judul tabel saat tabel kosong (PRD 14.9)', function () {
    Livewire::test(ListSiswa::class)
        ->assertSeeText(['Daftar Siswa', 'Belum ada data Siswa.']);
});
