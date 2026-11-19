<?php

use App\Enums\JenisLaporan;
use App\Enums\Tingkat;
use App\Http\PartialResponse;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create();
});

function urlLaporan(JenisLaporan $jenis, array $query = []): string
{
    return route('laporan.show', ['jenis' => $jenis, ...$query]);
}

describe('akses', function () {
    it('mengarahkan tamu ke halaman Login', function (JenisLaporan $jenis) {
        $this->get(urlLaporan($jenis))->assertRedirect(route('filament.admin.auth.login'));
    })->with(JenisLaporan::cases());

    it('menampilkan laporan untuk Admin dengan judul dan header tanpa cache', function (JenisLaporan $jenis) {
        $response = $this->actingAs($this->admin)->get(urlLaporan($jenis));

        $response->assertOk()
            ->assertSee($jenis->judul())
            ->assertSee($jenis->judulTabel());

        expect($response->headers->get('Cache-Control'))->toContain('no-store');
    })->with(JenisLaporan::cases());

    it('mengembalikan 404 untuk jenis laporan yang tidak dikenal', function () {
        $this->actingAs($this->admin)->get('/laporan/tidak-ada')->assertNotFound();
    });
});

describe('isi tabel', function () {
    it('mengurutkan Kelas berdasarkan Tingkat, Jurusan, lalu Nama Kelas', function () {
        Kelas::factory()->create(['nama_kelas' => 'XII IPA 1', 'tingkat' => Tingkat::XII, 'jurusan' => 'IPA']);
        Kelas::factory()->create(['nama_kelas' => 'X IPS 1', 'tingkat' => Tingkat::X, 'jurusan' => 'IPS']);
        Kelas::factory()->create(['nama_kelas' => 'XI IPA 1', 'tingkat' => Tingkat::XI, 'jurusan' => 'IPA']);
        Kelas::factory()->create(['nama_kelas' => 'X IPA 2', 'tingkat' => Tingkat::X, 'jurusan' => 'IPA']);
        Kelas::factory()->create(['nama_kelas' => 'X IPA 1', 'tingkat' => Tingkat::X, 'jurusan' => 'IPA']);

        $this->actingAs($this->admin)
            ->get(urlLaporan(JenisLaporan::SiswaPerKelas))
            ->assertSeeInOrder(['X IPA 1', 'X IPA 2', 'X IPS 1', 'XI IPA 1', 'XII IPA 1']);
    });

    it('menggabungkan nama Siswa aktif A–Z dipisah koma dan menyembunyikan Siswa di Trash', function () {
        $kelas = Kelas::factory()->create();
        Siswa::factory()->for($kelas)->create(['nama' => 'Citra']);
        Siswa::factory()->for($kelas)->create(['nama' => 'agus']);
        Siswa::factory()->for($kelas)->create(['nama' => 'Budi']);
        Siswa::factory()->for($kelas)->create(['nama' => 'Dimas Terhapus'])->delete();

        $this->actingAs($this->admin)
            ->get(urlLaporan(JenisLaporan::SiswaPerKelas))
            ->assertSee('agus, Budi, Citra')
            ->assertDontSee('Dimas Terhapus');
    });

    it('menampilkan Guru di setiap Kelas yang diajar dan menyembunyikan Guru di Trash', function () {
        $kelasA = Kelas::factory()->create(['nama_kelas' => 'X IPA 1', 'tingkat' => Tingkat::X, 'jurusan' => 'IPA']);
        $kelasB = Kelas::factory()->create(['nama_kelas' => 'XI IPA 1', 'tingkat' => Tingkat::XI, 'jurusan' => 'IPA']);

        Guru::factory()->create(['nama' => 'Bu Sari'])->kelas()->attach([$kelasA->id, $kelasB->id]);
        $terhapus = Guru::factory()->create(['nama' => 'Pak Terhapus']);
        $terhapus->kelas()->attach($kelasA->id);
        $terhapus->delete();

        $response = $this->actingAs($this->admin)->get(urlLaporan(JenisLaporan::GuruPerKelas));

        $response->assertSeeInOrder(['X IPA 1', 'Bu Sari', 'XI IPA 1', 'Bu Sari'])
            ->assertDontSee('Pak Terhapus');
    });

    it('tidak menampilkan Kelas yang berada di Trash', function () {
        Kelas::factory()->create(['nama_kelas' => 'X Aktif']);
        Kelas::factory()->create(['nama_kelas' => 'X Arsip'])->delete();

        $this->actingAs($this->admin)
            ->get(urlLaporan(JenisLaporan::Gabungan))
            ->assertSee('X Aktif')
            ->assertDontSee('X Arsip');
    });

    it('menampilkan kolom sesuai jenis laporan', function (JenisLaporan $jenis, bool $siswa, bool $guru) {
        Kelas::factory()->create();

        $response = $this->actingAs($this->admin)->get(urlLaporan($jenis));

        $siswa ? $response->assertSee('Daftar Siswa') : $response->assertDontSee('Daftar Siswa');
        $guru ? $response->assertSee('Daftar Guru') : $response->assertDontSee('Daftar Guru');
    })->with([
        'siswa per kelas' => [JenisLaporan::SiswaPerKelas, true, false],
        'guru per kelas' => [JenisLaporan::GuruPerKelas, false, true],
        'gabungan' => [JenisLaporan::Gabungan, true, true],
    ]);
});

describe('empty state', function () {
    it('menampilkan pesan khusus jika tidak ada Kelas aktif, dengan header tetap terlihat', function (JenisLaporan $jenis) {
        $this->actingAs($this->admin)
            ->get(urlLaporan($jenis))
            ->assertSee($jenis->labelKolomKelas())
            ->assertSee('Tidak ada data kelas yang tersedia untuk ditampilkan.');
    })->with(JenisLaporan::cases());

    it('menampilkan "Belum ada data" untuk Kelas tanpa Siswa maupun Guru', function () {
        Kelas::factory()->create();

        $html = $this->actingAs($this->admin)->get(urlLaporan(JenisLaporan::Gabungan))->getContent();

        expect(substr_count($html, 'Belum ada data'))->toBe(2);
    });
});

describe('pagination', function () {
    beforeEach(function () {
        foreach (range(1, 12) as $n) {
            Kelas::factory()->create([
                'nama_kelas' => sprintf('X IPA %02d', $n),
                'tingkat' => Tingkat::X,
                'jurusan' => 'IPA',
            ]);
        }
    });

    it('menampilkan 10 Kelas per halaman beserta ringkasan jumlah data', function () {
        $this->actingAs($this->admin)
            ->get(urlLaporan(JenisLaporan::SiswaPerKelas))
            ->assertSee('X IPA 10')
            ->assertDontSee('X IPA 11')
            ->assertSee('Menampilkan 1–10 dari 12 data');

        $this->actingAs($this->admin)
            ->get(urlLaporan(JenisLaporan::SiswaPerKelas, ['page' => 2]))
            ->assertSee('X IPA 12')
            ->assertSee('Menampilkan 11–12 dari 12 data');
    });

    it('mengarahkan halaman di luar jangkauan ke halaman terakhir yang valid', function () {
        $this->actingAs($this->admin)
            ->get(urlLaporan(JenisLaporan::SiswaPerKelas, ['page' => 9]))
            ->assertRedirect(urlLaporan(JenisLaporan::SiswaPerKelas, ['page' => 2]));
    });

    it('mengarahkan nilai page yang tidak valid ke halaman pertama', function (mixed $page) {
        $this->actingAs($this->admin)
            ->get(urlLaporan(JenisLaporan::SiswaPerKelas, ['page' => $page]))
            ->assertRedirect(urlLaporan(JenisLaporan::SiswaPerKelas));
    })->with(['abc', 0, -1]);

    it('mengembalikan hanya fragmen tabel untuk permintaan parsial', function () {
        $response = $this->actingAs($this->admin)
            ->withHeader(PartialResponse::HEADER, PartialResponse::TABEL)
            ->get(urlLaporan(JenisLaporan::SiswaPerKelas, ['page' => 2]));

        $response->assertOk()
            ->assertSee('X IPA 12')
            ->assertDontSee('<html', escape: false)
            ->assertDontSee('Navigasi utama');

        expect($response->headers->get('Vary'))->toContain(PartialResponse::HEADER);
    });

    it('membalas 422 JSON standar Laravel untuk permintaan parsial dengan page tidak valid', function () {
        $this->actingAs($this->admin)
            ->withHeader(PartialResponse::HEADER, PartialResponse::TABEL)
            ->get(urlLaporan(JenisLaporan::SiswaPerKelas, ['page' => 'abc']))
            ->assertUnprocessable()
            ->assertJsonStructure(['message', 'errors' => ['page']]);
    });
});
