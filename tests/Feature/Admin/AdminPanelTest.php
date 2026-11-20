<?php

use App\Actions\Auth\AuthenticateUserAction;
use App\Actions\Auth\LogoutUserAction;
use App\Enums\JenisLaporan;
use App\Filament\Pages\Auth\Login;
use App\Filament\Pages\Laporan;
use App\Filament\Widgets\DistribusiKelasWidget;
use App\Filament\Widgets\RingkasanSekolahWidget;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Livewire\Topbar;
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
});
