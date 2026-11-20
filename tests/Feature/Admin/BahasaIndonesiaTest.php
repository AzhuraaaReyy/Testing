<?php

use App\Enums\JenisKelamin;
use App\Filament\Pages\Auth\Login;
use App\Filament\Resources\Guru\Pages\CreateGuru;
use App\Filament\Resources\Kelas\Pages\ListKelas;
use App\Filament\Resources\Siswa\Pages\CreateSiswa;
use App\Filament\Resources\Siswa\Pages\EditSiswa;
use App\Filament\Resources\Siswa\Pages\ListSiswa;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;

/*
| Seluruh respons yang dilihat Admin konsisten berbahasa Indonesia dan memakai istilah PRD:
| Tambah, Detail, Edit, Hapus, Pulihkan, Simpan, Batal, Logout.
*/

describe('istilah halaman & tombol', function () {
    beforeEach(fn () => $this->actingAs(User::factory()->create()));

    it('memakai istilah PRD pada judul halaman', function (string $halaman, string $judul) {
        $siswa = Siswa::factory()->create(['nama' => 'Ani Lestari']);
        $url = ['daftar' => '/admin/siswa', 'tambah' => '/admin/siswa/create', 'edit' => "/admin/siswa/{$siswa->id}/edit", 'detail' => "/admin/siswa/{$siswa->id}"][$halaman];

        $this->get($url)->assertOk()->assertSeeText($judul);
    })->with([
        ['daftar', 'Tambah Siswa'],
        ['tambah', 'Tambah Siswa'],
        // Judul Edit/Detail menyebut data yang sedang dibuka.
        ['edit', 'Edit Ani Lestari'],
        ['detail', 'Detail Ani Lestari'],
    ]);

    it('menampilkan tombol Simpan & Batal tanpa "tambah lainnya" di form Tambah', function () {
        $this->get('/admin/siswa/create')
            ->assertSeeText(['Simpan', 'Batal'])
            ->assertDontSeeText('tambah lainnya')
            ->assertDontSeeText('Buat');
    });

    it('memakai label Logout di menu akun', function () {
        $this->get('/admin')->assertSeeText('Logout')->assertDontSeeText('Keluar</span>');
    });

    it('mematikan validasi bawaan browser agar pesan selalu dari server', function () {
        $this->get('/admin/siswa/create')->assertSee('novalidate', escape: false);
    });
});

describe('notifikasi berhasil', function () {
    beforeEach(fn () => $this->actingAs(User::factory()->create()));

    it('memberi tahu setelah Tambah dan Edit', function () {
        $kelas = Kelas::factory()->create();

        Livewire::test(CreateSiswa::class)
            ->fillForm(['nis' => '001', 'nama' => 'Ani', 'jenis_kelamin' => JenisKelamin::Perempuan->value, 'alamat' => 'Jl. A', 'kelas_id' => $kelas->id])
            ->call('create')
            ->assertNotified('Data berhasil ditambahkan.');

        Livewire::test(EditSiswa::class, ['record' => Siswa::query()->sole()->id])
            ->fillForm(['nama' => 'Ani Lestari'])
            ->call('save')
            ->assertNotified('Perubahan berhasil disimpan.');
    });

    it('memberi tahu setelah Hapus dan Pulihkan', function () {
        $kelas = Kelas::factory()->create();

        Livewire::test(ListKelas::class)
            ->callAction(TestAction::make('delete')->table($kelas))
            ->assertNotified('Data berhasil dipindahkan ke Trash.');

        Livewire::test(ListKelas::class)
            ->set('activeTab', 'trash')
            ->callAction(TestAction::make('restore')->table($kelas))
            ->assertNotified('Data berhasil dipulihkan.');
    });

    it('menjelaskan akibat hapus pada dialog konfirmasi', function () {
        $siswa = Siswa::factory()->create();

        // Isi modal dimuat terpisah (partial), jadi konfigurasinya diperiksa dari aksi yang ter-mount.
        $aksi = Livewire::test(ListSiswa::class)
            ->mountAction(TestAction::make('delete')->table($siswa))
            ->instance()
            ->getMountedAction();

        // Judul menyebut data yang akan dihapus, konsisten dengan judul Edit/Detail.
        expect($aksi->getModalHeading())->toBe("Hapus {$siswa->nama}")
            ->and($aksi->getModalDescription())->toBe('Data akan dipindahkan ke Trash dan dapat dipulihkan kembali.')
            ->and($aksi->getModalSubmitAction()->getLabel())->toBe('Ya, Hapus')
            ->and($aksi->getModalCancelAction()->getLabel())->toBe('Batal');
    });
});

describe('validasi form', function () {
    beforeEach(fn () => $this->actingAs(User::factory()->create()));

    it('menampilkan pesan Bahasa Indonesia di bawah field', function () {
        Livewire::test(CreateGuru::class)
            ->fillForm(['nip' => '', 'nama' => '', 'jenis_kelamin' => null, 'alamat' => '', 'no_telepon' => '12345', 'kelas' => []])
            ->call('create')
            ->assertSeeText([
                'Nama Guru wajib diisi.',
                'Jenis Kelamin wajib diisi.',
                'Alamat wajib diisi.',
                'Format No. Telepon tidak valid. Gunakan 08… atau +628….',
                'Kelas yang Diajar wajib diisi.',
            ])
            ->assertDontSeeText('field is required');
    });

    it('memvalidasi field saat ditinggalkan, sebelum tombol Simpan ditekan', function () {
        Livewire::test(CreateSiswa::class)
            ->set('data.nis', '')
            ->assertHasFormErrors(['nis'])
            ->assertSeeText('NIS wajib diisi.');
    });
});

it('tetap menyimpan isian field lain saat satu field tidak valid', function () {
    $this->actingAs(User::factory()->create());

    // Validasi live tidak boleh menghentikan pembaruan field lain dalam request yang sama.
    Livewire::test(CreateGuru::class)
        ->fillForm(['nama' => '', 'no_telepon' => '081234567890'])
        ->assertSet('data.no_telepon', '081234567890')
        ->assertHasFormErrors(['nama']);
});

it('memvalidasi format email login Admin sebelum dikirim (PRD 4.3)', function () {
    Livewire::test(Login::class)
        ->set('data.email', 'bukan-email')
        ->assertHasFormErrors(['email'])
        ->assertSeeText('Format email tidak valid.');
});

describe('halaman error', function () {
    it('menampilkan 404 berbahasa Indonesia', function () {
        $this->get('/halaman-yang-tidak-ada')
            ->assertNotFound()
            ->assertSeeText(['Halaman tidak ditemukan', 'Ke halaman login'])
            ->assertDontSeeText('Not Found');
    });

    it('menampilkan 403 berbahasa Indonesia untuk Guru yang membuka panel Admin', function () {
        $this->actingAs(User::factory()->guru()->create())
            ->get('/admin')
            ->assertForbidden()
            ->assertSeeText(['Akses ditolak', 'Kembali ke beranda'])
            ->assertDontSeeText('Forbidden');
    });
});
