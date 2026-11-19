<?php

use App\DTOs\Dashboard\RingkasanSekolahData;
use App\Enums\JenisLaporan;
use App\Enums\Tingkat;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;

describe('pemisahan akses per peran', function () {
    it('mengarahkan Guru/Siswa yang membuka laporan Admin ke portal', function (string $state) {
        $this->actingAs(User::factory()->{$state}()->create())
            ->get(route('laporan.show', JenisLaporan::SiswaPerKelas))
            ->assertRedirect(route('portal.dashboard'));
    })->with(['guru', 'siswa']);

    it('menolak Guru/Siswa membuka panel Filament', function (string $state) {
        $this->actingAs(User::factory()->{$state}()->create())
            ->get('/admin')
            ->assertForbidden();
    })->with(['guru', 'siswa']);

    it('mengarahkan Admin yang membuka portal ke panel Admin', function () {
        $this->actingAs(User::factory()->create())
            ->get(route('portal.dashboard'))
            ->assertRedirect(route('filament.admin.pages.dashboard'));
    });

    it('mengakhiri session Siswa yang profilnya masuk Trash', function () {
        $siswa = Siswa::factory()->create();
        $user = User::factory()->siswa($siswa)->create();
        $siswa->delete();

        $this->actingAs($user)->get(route('portal.dashboard'))->assertRedirect(route('login'));
        $this->assertGuest();
    });
});

describe('dashboard portal', function () {
    it('menampilkan total Siswa, Kelas, dan Guru aktif', function () {
        $kelas = Kelas::factory()->count(2)->create();
        Siswa::factory()->count(3)->for($kelas->first())->create();
        Siswa::factory()->for($kelas->first())->create()->delete();
        Guru::factory()->create()->kelas()->attach($kelas->first());

        $user = User::factory()->guru()->create(); // menambah 1 Guru

        $this->actingAs($user)
            ->get(route('portal.dashboard'))
            ->assertOk()
            ->assertSeeInOrder(['Total Siswa', 'Total Kelas', 'Total Guru'])
            ->assertViewHas('ringkasan', fn (RingkasanSekolahData $r): bool => $r->totalSiswa === 3 && $r->totalKelas === 2 && $r->totalGuru === 2)
            ->assertDontSee('Data Master');
    });
});

describe('laporan portal hanya Kelas sendiri', function () {
    beforeEach(function () {
        $this->kelasA = Kelas::factory()->create(['nama_kelas' => 'X IPA 1', 'tingkat' => Tingkat::X, 'jurusan' => 'IPA']);
        $this->kelasB = Kelas::factory()->create(['nama_kelas' => 'XI IPS 1', 'tingkat' => Tingkat::XI, 'jurusan' => 'IPS']);
        $this->kelasC = Kelas::factory()->create(['nama_kelas' => 'XII IPA 1', 'tingkat' => Tingkat::XII, 'jurusan' => 'IPA']);
    });

    it('Siswa hanya melihat Kelasnya sendiri', function () {
        $siswa = Siswa::factory()->for($this->kelasB)->create();

        $this->actingAs(User::factory()->siswa($siswa)->create())
            ->get(route('portal.laporan.show', JenisLaporan::Gabungan))
            ->assertOk()
            ->assertSee('XI IPS 1')
            ->assertDontSee('X IPA 1')
            ->assertDontSee('XII IPA 1')
            ->assertSee('Menampilkan 1–1 dari 1 data');
    });

    it('Guru hanya melihat Kelas yang diajar', function () {
        $guru = Guru::factory()->create();
        $guru->kelas()->attach([$this->kelasA->id, $this->kelasC->id]);

        $this->actingAs(User::factory()->guru($guru)->create())
            ->get(route('portal.laporan.show', JenisLaporan::SiswaPerKelas))
            ->assertOk()
            ->assertSeeInOrder(['X IPA 1', 'XII IPA 1'])
            ->assertDontSee('XI IPS 1');
    });

    it('menyamakan judul halaman dengan nama menu yang aktif', function () {
        $siswa = Siswa::factory()->for($this->kelasA)->create();

        $html = $this->actingAs(User::factory()->siswa($siswa)->create())
            ->get(route('portal.laporan.show', JenisLaporan::GuruPerKelas))
            ->assertSee('<title>Guru Pengajar', escape: false)
            ->assertSee('Daftar Guru yang mengajar di Kelas Anda.')
            ->assertSee(route('logout'), escape: false)
            ->getContent();

        preg_match('/aria-current="page"[^>]*>.*?nav-label[^>]*>([^<]+)/s', $html, $aktif);
        expect(trim($aktif[1] ?? ''))->toBe('Guru Pengajar');
    });
});
