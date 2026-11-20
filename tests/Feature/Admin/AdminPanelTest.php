<?php

use App\Actions\Auth\AuthenticateUserAction;
use App\Actions\Auth\LogoutUserAction;
use App\Enums\JenisLaporan;
use App\Enums\Tingkat;
use App\Filament\Pages\Auth\Login;
use App\Filament\Pages\Laporan;
use App\Filament\Resources\Guru\GuruResource;
use App\Filament\Resources\Guru\Pages\ListGuru;
use App\Filament\Resources\Kelas\Pages\ListKelas;
use App\Filament\Resources\Siswa\Pages\ListSiswa;
use App\Filament\Resources\Siswa\SiswaResource;
use App\Filament\Widgets\DistribusiKelasWidget;
use App\Filament\Widgets\RingkasanSekolahWidget;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Livewire\Topbar;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Vite;
use Livewire\Livewire;

describe('Login Admin', function () {
    it('meloginkan Admin', function () {
        $admin = User::factory()->create();

        Livewire::test(Login::class)
            ->fillForm(['email' => $admin->email, 'password' => 'password'])
            ->call('authenticate')
            ->assertHasNoFormErrors();

        $this->assertAuthenticatedAs($admin);
    });

    it('menolak akun Guru/Siswa dengan pesan umum', function (string $state) {
        $user = User::factory()->{$state}()->create();

        Livewire::test(Login::class)
            ->fillForm(['email' => $user->email, 'password' => 'password'])
            ->call('authenticate')
            ->assertHasFormErrors(['email' => AuthenticateUserAction::GAGAL]);

        $this->assertGuest();
    })->with(['guru', 'siswa']);

    it('membatasi login setelah tiga kegagalan', function () {
        $admin = User::factory()->create();
        $login = fn (string $password) => Livewire::test(Login::class)
            ->fillForm(['email' => $admin->email, 'password' => $password])
            ->call('authenticate');

        foreach (range(1, AuthenticateUserAction::MAX_ATTEMPTS) as $_) {
            $login('salah');
        }

        $login('password')->assertSeeText('Terlalu banyak percobaan login');
        $this->assertGuest();
    });

    it('tidak menampilkan opsi Ingat Saya', function () {
        Livewire::test(Login::class)->assertFormFieldDoesNotExist('remember');
    });

    it('menolak akun Guru/Siswa membuka panel Admin', function () {
        $this->actingAs(User::factory()->guru()->create())
            ->get('/admin')
            ->assertForbidden();
    });
});

it('membuka seluruh halaman CRUD, termasuk Detail data di Trash', function (string $slug, Closure $buat) {
    $this->actingAs(User::factory()->create());
    [$aktif, $trash] = [$buat(), $buat()];
    $trash->delete();

    foreach (["/admin/{$slug}", "/admin/{$slug}/create", "/admin/{$slug}/{$aktif->id}", "/admin/{$slug}/{$aktif->id}/edit", "/admin/{$slug}/{$trash->id}"] as $url) {
        $this->get($url)->assertOk();
    }

    // Data di Trash harus dipulihkan dulu sebelum dapat diedit.
    $this->get("/admin/{$slug}/{$trash->id}/edit")->assertForbidden();
})->with([
    'siswa' => ['siswa', fn () => User::factory()->siswa()->create()->siswa],
    'kelas' => ['kelas', fn () => Kelas::factory()->create()],
    'guru' => ['guru', fn () => Guru::factory()->hasAttached(Kelas::factory())->create()],
]);

describe('Shell panel Admin', function () {
    beforeEach(function () {
        $this->admin = User::factory()->create(['name' => 'Admin Sekolah', 'email' => 'admin@sekolah.test']);
        $this->actingAs($this->admin);
    });

    it('menyiapkan error state tabel "Coba Lagi" di halaman bertabel', function (Closure $url) {
        $this->get($url())
            ->assertOk()
            ->assertSee('data-table-error-template', escape: false)
            ->assertSeeText(['Data gagal dimuat. Silakan coba lagi.', 'Coba Lagi'])
            ->assertSee(Vite::asset('resources/js/filament/admin.ts'), escape: false);
    })->with([
        'CRUD' => [fn (): string => '/admin/siswa'],
        'Dashboard' => [fn (): string => '/admin'],
        'Laporan' => [fn (): string => Laporan::getUrl(['jenis' => JenisLaporan::Gabungan])],
    ]);

    it('menandai area tabel laporan agar bisa diganti error state', function () {
        $this->get(Laporan::getUrl(['jenis' => JenisLaporan::Gabungan]))->assertSee('data-table-area', escape: false);
    });

    it('menampilkan nama dan email Admin di menu akun', function () {
        $this->get('/admin')->assertSeeText(['Admin Sekolah', 'admin@sekolah.test']);
    });

    it('meminta konfirmasi sebelum logout lalu menampilkan pesan berhasil', function () {
        // Di aplikasi, panel aktif di-set middleware panel; di test diset manual.
        Filament::setCurrentPanel('admin');
        $logout = filament()->getUserMenuItems()['logout'];

        expect($logout->getModalDescription())->toBe('Apakah Anda yakin ingin keluar dari sistem?')
            ->and($logout->getModalSubmitActionLabel())->toBe('Ya, Logout');

        Livewire::test(Topbar::class)
            ->mountAction('logout')
            // Masih ter-mount = menunggu konfirmasi, belum logout.
            ->assertActionMounted('logout')
            ->tap(fn () => $this->assertAuthenticatedAs($this->admin))
            ->callMountedAction()
            ->assertNotified(LogoutUserAction::BERHASIL)
            ->assertRedirect(filament()->getLoginUrl());

        $this->assertGuest();
    });

    it('menampilkan tiga laporan di menu Laporan dan menandai hanya yang sedang dibuka', function () {
        $url = Laporan::getUrl(['jenis' => JenisLaporan::GuruPerKelas]);

        $html = $this->get($url)
            ->assertOk()
            ->assertSeeInOrder(['Data Master', 'Siswa', 'Kelas', 'Guru', 'Laporan', 'Siswa per Kelas', 'Guru per Kelas', 'Siswa, Kelas &amp; Guru'], escape: false)
            ->getContent();

        preg_match_all('/<li[^>]*fi-sidebar-item[^>]*fi-active[^>]*>\s*<a[^>]*href="([^"]+)"/', $html, $aktif);

        expect($aktif[1])->toBe([$url]);
    });
});

describe('Dashboard Admin', function () {
    beforeEach(fn () => $this->actingAs(User::factory()->create()));

    it('menampilkan judul, subjudul, dan tiga kartu ringkasan', function () {
        $this->get('/admin')
            ->assertOk()
            ->assertSeeText('Ringkasan data Siswa, Kelas, dan Guru.');
    });

    it('menghitung hanya data aktif pada kartu ringkasan', function () {
        $kelas = Kelas::factory()->count(2)->create();
        Siswa::factory()->count(3)->for($kelas->first())->create();
        Siswa::factory()->for($kelas->first())->create()->delete();
        Guru::factory()->create()->delete();

        Livewire::test(RingkasanSekolahWidget::class)
            ->assertSeeInOrder(['Total Siswa', '3', 'Total Kelas', '2', 'Total Guru', '0'])
            // Kartu Admin menjadi shortcut ke CRUD terkait (PRD 11.3).
            ->assertSeeHtml('href="'.url('/admin/siswa').'"')
            ->assertSeeHtml('href="'.url('/admin/kelas').'"')
            ->assertSeeHtml('href="'.url('/admin/guru').'"');
    });

    it('menampilkan seluruh Kelas aktif termasuk yang berjumlah 0', function (string $relasi) {
        $kosong = Kelas::factory()->create();
        $berisi = Kelas::factory()->create();
        $relasi === 'siswa'
            ? Siswa::factory()->count(2)->for($berisi)->create()
            : Guru::factory()->count(2)->hasAttached($berisi)->create();

        Livewire::test(DistribusiKelasWidget::class, ['relasi' => $relasi])
            ->assertCanSeeTableRecords([$kosong, $berisi])
            ->assertTableColumnStateSet("{$relasi}_count", 0, $kosong)
            ->assertTableColumnStateSet("{$relasi}_count", 2, $berisi);
    })->with(['siswa', 'guru']);

    it('memakai pagination 10 Kelas per halaman seperti tabel lain', function (string $relasi) {
        Kelas::factory()->count(12)->create();
        $urutan = Kelas::query()->urutanTampilan()->get();

        Livewire::test(DistribusiKelasWidget::class, ['relasi' => $relasi])
            ->assertCanSeeTableRecords($urutan->take(10), inOrder: true)
            ->assertCanNotSeeTableRecords($urutan->slice(10))
            ->assertSeeText('Menampilkan 1–10 dari 12 data')
            ->call('gotoPage', 2, 'distribusi'.ucfirst($relasi).'Page')
            ->assertCanSeeTableRecords($urutan->slice(10))
            ->assertSeeText('Menampilkan 11–12 dari 12 data');
    })->with(['siswa', 'guru']);

    it('memisahkan parameter halaman kedua tabel distribusi di URL', function () {
        Kelas::factory()->count(12)->create();

        $siswa = Livewire::withQueryParams(['distribusiGuruPage' => 2])->test(DistribusiKelasWidget::class, ['relasi' => 'siswa']);
        $guru = Livewire::withQueryParams(['distribusiGuruPage' => 2])->test(DistribusiKelasWidget::class, ['relasi' => 'guru']);

        $siswa->assertSeeText('Menampilkan 1–10 dari 12 data');
        $guru->assertSeeText('Menampilkan 11–12 dari 12 data');
    });
});

describe('Kolom Aksi', function () {
    it('memberi judul "Aksi" pada kolom tombol di setiap tabel Data Master', function (string $halaman) {
        $this->actingAs(User::factory()->create());
        Siswa::factory()->create();
        Guru::factory()->hasAttached(Kelas::factory())->create();

        expect(Livewire::test($halaman)->instance()->getTable()->getRecordActionsColumnLabel())->toBe('Aksi');
        expect(Livewire::test($halaman)->html())->toMatch('/<th[^>]*>\s*Aksi\s*<\/th>/');
    })->with([
        'Siswa' => ListSiswa::class,
        'Kelas' => ListKelas::class,
        'Guru' => ListGuru::class,
    ]);

    it('tidak menampilkan kolom Aksi pada tabel tanpa tombol (Dashboard)', function () {
        Kelas::factory()->create();

        expect(Livewire::test(DistribusiKelasWidget::class, ['relasi' => 'siswa'])->html())->not->toMatch('/<th[^>]*>\s*Aksi\s*<\/th>/');
    });
});

describe('Badge Tingkat & Kelas', function () {
    /** Kelas CSS badge Laporan (x-atoms.label-badge); harus sama persis di panel Admin. */
    function kelasBadgeLaporan(): string
    {
        preg_match('/<span class="([^"]+)">\s*X\s*<\/span>/', Blade::render('<x-atoms.tingkat-badge :tingkat="$t" />', ['t' => Tingkat::X]), $cocok);

        return $cocok[1];
    }

    it('memakai badge yang sama dengan Laporan, bukan badge berwarna Filament', function (string $halaman, array $param, string $teks) {
        $this->actingAs(User::factory()->create());
        $kelas = Kelas::factory()->create(['nama_kelas' => 'X IPA 9', 'tingkat' => Tingkat::X]);
        Guru::factory()->hasAttached($kelas)->create();
        Siswa::factory()->for($kelas)->create();

        $html = Livewire::test($halaman, $param)->html();

        expect($html)->toContain('<span class="'.kelasBadgeLaporan().'">')
            ->and($html)->toMatch('/<span class="'.preg_quote(kelasBadgeLaporan(), '/').'">\s*'.preg_quote($teks, '/').'\s*<\/span>/')
            ->and($html)->not->toContain('fi-badge');
    })->with([
        'Tabel Kelas (Tingkat)' => [ListKelas::class, [], 'X'],
        'Dashboard (Tingkat)' => [DistribusiKelasWidget::class, ['relasi' => 'siswa'], 'X'],
        'Tabel Guru (Kelas)' => [ListGuru::class, [], 'X IPA 9'],
        'Tabel Siswa (Kelas)' => [ListSiswa::class, [], 'X IPA 9'],
    ]);

    it('menampilkan beberapa Kelas Guru sebagai badge berjajar tanpa koma', function () {
        $this->actingAs(User::factory()->create());
        $a = Kelas::factory()->create(['nama_kelas' => 'X IPA 2', 'tingkat' => Tingkat::X]);
        $b = Kelas::factory()->create(['nama_kelas' => 'XI IPS 1', 'tingkat' => Tingkat::XI]);
        $guru = Guru::factory()->hasAttached([$b, $a])->create();
        $badge = '<span class="'.kelasBadgeLaporan().'">';

        foreach ([Livewire::test(ListGuru::class)->html(), $this->get(GuruResource::getUrl('view', ['record' => $guru]))->getContent()] as $html) {
            // Urut Tingkat (X → XI), keduanya badge, tidak dipisah koma.
            expect($html)->toMatch('/'.preg_quote($badge, '/').'\s*X IPA 2\s*<\/span>\s*'.preg_quote($badge, '/').'\s*XI IPS 1\s*<\/span>/');
        }
    });

    it('menampilkan badge Kelas di halaman detail Siswa', function () {
        $this->actingAs(User::factory()->create());
        $siswa = Siswa::factory()->for(Kelas::factory()->state(['nama_kelas' => 'XII IPA 3']))->create();

        $this->get(SiswaResource::getUrl('view', ['record' => $siswa]))
            ->assertOk()
            ->assertSee('<span class="'.kelasBadgeLaporan().'">', escape: false)
            ->assertSeeText('XII IPA 3');
    });

    it('tidak menambah query per baris di tabel Guru (relasi Kelas dimuat sekaligus)', function () {
        $this->actingAs(User::factory()->create());
        $kelas = Kelas::factory()->count(3)->create();

        $hitung = function () {
            DB::flushQueryLog();
            DB::enableQueryLog();
            Livewire::test(ListGuru::class);

            return count(DB::getQueryLog());
        };

        Guru::factory()->count(2)->hasAttached($kelas)->create();
        $duaGuru = $hitung();
        Guru::factory()->count(8)->hasAttached($kelas)->create();

        expect($hitung())->toBe($duaGuru);
    });

    it('meng-escape nama Kelas di dalam badge', function (string $halaman) {
        $this->actingAs(User::factory()->create());
        $kelas = Kelas::factory()->create(['nama_kelas' => '<b>XSS</b>']);
        Guru::factory()->hasAttached($kelas)->create();
        Siswa::factory()->for($kelas)->create();

        expect(Livewire::test($halaman)->html())->toContain('&lt;b&gt;XSS&lt;/b&gt;')->not->toContain('<b>XSS</b>');
    })->with(['Guru' => ListGuru::class, 'Siswa' => ListSiswa::class]);
});
