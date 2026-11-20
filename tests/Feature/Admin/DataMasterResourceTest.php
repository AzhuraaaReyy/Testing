<?php

use App\Enums\JenisKelamin;
use App\Enums\Role;
use App\Enums\Tingkat;
use App\Filament\Resources\Guru\Pages\CreateGuru;
use App\Filament\Resources\Guru\Pages\ListGuru;
use App\Filament\Resources\Kelas\Pages\CreateKelas;
use App\Filament\Resources\Kelas\Pages\EditKelas;
use App\Filament\Resources\Kelas\Pages\ListKelas;
use App\Filament\Resources\Siswa\Pages\CreateSiswa;
use App\Filament\Resources\Siswa\Pages\EditSiswa;
use App\Filament\Resources\Siswa\Pages\ListSiswa;
use App\Filament\Resources\Siswa\SiswaResource;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;

beforeEach(fn () => $this->actingAs(User::factory()->create()));

/**
 * @return array<string, mixed>
 */
function dataSiswa(array $override = []): array
{
    return [
        'nis' => '00123',
        'nama' => 'Ani Lestari',
        'jenis_kelamin' => JenisKelamin::Perempuan->value,
        'alamat' => 'Jl. Melati 1',
        'kelas_id' => Kelas::factory()->create()->id,
        ...$override,
    ];
}

/**
 * @return array<string, mixed>
 */
function dataGuru(array $override = []): array
{
    return [
        'nip' => '',
        'nama' => 'Budi Santoso',
        'jenis_kelamin' => JenisKelamin::LakiLaki->value,
        'alamat' => 'Jl. Kenanga 2',
        'no_telepon' => '+6281234567890',
        'kelas' => Kelas::factory()->count(2)->create()->modelKeys(),
        ...$override,
    ];
}

describe('Kelas', function () {
    it('menambah Kelas lalu kembali ke daftar', function () {
        Livewire::test(CreateKelas::class)
            ->fillForm(['nama_kelas' => 'X IPA 1', 'tingkat' => Tingkat::X->value, 'jurusan' => 'IPA'])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertRedirect(route('filament.admin.resources.kelas.index'));

        expect(Kelas::query()->where('nama_kelas', 'X IPA 1')->exists())->toBeTrue();
    });

    it('menolak Nama Kelas yang sudah dipakai Kelas di Trash', function () {
        Kelas::factory()->create(['nama_kelas' => 'X IPA 1'])->delete();

        Livewire::test(CreateKelas::class)
            ->fillForm(['nama_kelas' => 'X IPA 1', 'tingkat' => Tingkat::X->value])
            ->call('create')
            ->assertHasFormErrors(['nama_kelas' => 'unique']);
    });

    it('mengizinkan Nama Kelas milik record yang sedang diedit', function () {
        $kelas = Kelas::factory()->create(['nama_kelas' => 'X IPA 1']);

        Livewire::test(EditKelas::class, ['record' => $kelas->getRouteKey()])
            ->fillForm(['keterangan' => 'Ruang 101'])
            ->call('save')
            ->assertHasNoFormErrors();

        expect($kelas->fresh()->keterangan)->toBe('Ruang 101');
    });

    it('menolak hapus Kelas yang masih dipakai dan menampilkan alasannya', function () {
        $kelas = Kelas::factory()->create();
        Siswa::factory()->for($kelas)->create();

        Livewire::test(ListKelas::class)
            ->callAction(TestAction::make('delete')->table($kelas))
            ->assertNotified('Kelas masih memiliki Siswa aktif.');

        expect($kelas->fresh()->trashed())->toBeFalse();
    });

    it('mengurutkan tabel Tingkat X, XI, XII', function () {
        $xii = Kelas::factory()->create(['tingkat' => Tingkat::XII]);
        $x = Kelas::factory()->create(['tingkat' => Tingkat::X]);
        $xi = Kelas::factory()->create(['tingkat' => Tingkat::XI]);

        Livewire::test(ListKelas::class)->assertCanSeeTableRecords([$x, $xi, $xii], inOrder: true);
    });
});

describe('Siswa', function () {
    it('menambah Siswa dengan NIS ber-nol di awal dan akun portal', function () {
        Livewire::test(CreateSiswa::class)
            ->fillForm(dataSiswa(['akun' => ['email' => 'ani@sekolah.test', 'password' => 'rahasia123']]))
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertRedirect(SiswaResource::getUrl());

        $siswa = Siswa::query()->sole();
        $akun = User::query()->where('email', 'ani@sekolah.test')->sole();

        expect($siswa->nis)->toBe('00123')
            ->and($akun->role)->toBe(Role::Siswa)
            ->and($akun->siswa_id)->toBe($siswa->id)
            ->and($akun->name)->toBe('Ani Lestari');
    });

    it('tidak membuat akun bila email dikosongkan', function () {
        Livewire::test(CreateSiswa::class)
            ->fillForm(dataSiswa())
            ->call('create')
            ->assertHasNoFormErrors();

        expect(User::query()->where('role', Role::Siswa)->exists())->toBeFalse();
    });

    it('mewajibkan password saat membuat akun baru', function () {
        Livewire::test(CreateSiswa::class)
            ->fillForm(dataSiswa(['akun' => ['email' => 'ani@sekolah.test']]))
            ->call('create')
            ->assertHasFormErrors(['akun.password' => 'required']);
    });

    it('mempertahankan password lama dan menyinkronkan nama akun saat edit', function () {
        $akun = User::factory()->siswa()->create();
        $hashLama = $akun->password;

        Livewire::test(EditSiswa::class, ['record' => $akun->siswa_id])
            ->fillForm(['nama' => 'Nama Baru'])
            ->call('save')
            ->assertHasNoFormErrors();

        expect($akun->fresh())->password->toBe($hashLama)->name->toBe('Nama Baru');
    });

    it('menolak NIS duplikat termasuk milik Siswa di Trash', function () {
        Siswa::factory()->create(['nis' => '00123'])->delete();

        Livewire::test(CreateSiswa::class)
            ->fillForm(dataSiswa())
            ->call('create')
            ->assertHasFormErrors(['nis' => 'unique']);
    });

    it('hanya menerima Kelas aktif', function () {
        $kelasTrash = Kelas::factory()->create();
        $kelasTrash->delete();

        Livewire::test(CreateSiswa::class)
            ->fillForm(dataSiswa(['kelas_id' => $kelasTrash->id]))
            ->call('create')
            ->assertHasFormErrors(['kelas_id' => 'in']);
    });

    it('menampilkan Siswa terhapus hanya di tab Trash tanpa aksi Edit', function () {
        $aktif = Siswa::factory()->create();
        $trash = Siswa::factory()->create();
        $trash->delete();

        Livewire::test(ListSiswa::class)
            ->assertCanSeeTableRecords([$aktif])
            ->assertCanNotSeeTableRecords([$trash])
            ->set('activeTab', 'trash')
            ->assertCanSeeTableRecords([$trash])
            ->assertCanNotSeeTableRecords([$aktif])
            ->assertActionHidden(TestAction::make('edit')->table($trash));
    });

    it('mewajibkan memilih Kelas aktif saat memulihkan Siswa', function () {
        $siswa = Siswa::factory()->create();
        $siswa->delete();
        $kelas = Kelas::factory()->create();

        $daftarTrash = fn () => Livewire::test(ListSiswa::class)->set('activeTab', 'trash');

        $daftarTrash()
            ->callAction(TestAction::make('restore')->table($siswa), data: ['kelas_id' => null])
            ->assertHasActionErrors(['kelas_id' => 'required']);

        expect($siswa->fresh()->trashed())->toBeTrue();

        $daftarTrash()
            ->callAction(TestAction::make('restore')->table($siswa), data: ['kelas_id' => $kelas->id])
            ->assertHasNoActionErrors();

        expect($siswa->fresh())->trashed()->toBeFalse()->kelas_id->toBe($kelas->id);
    });
});

describe('Guru', function () {
    it('menambah Guru dengan banyak Kelas dan NIP kosong tersimpan sebagai NULL', function () {
        Guru::factory()->create(['nip' => null]);
        $data = dataGuru();

        Livewire::test(CreateGuru::class)
            ->fillForm($data)
            ->call('create')
            ->assertHasNoFormErrors();

        $guru = Guru::query()->where('nama', 'Budi Santoso')->sole();

        expect($guru->nip)->toBeNull()
            ->and($guru->kelas()->pluck('kelas.id')->sort()->values()->all())->toBe($data['kelas']);
    });

    it('memvalidasi No. Telepon format Indonesia dan minimal satu Kelas', function (string $telepon, bool $valid) {
        $test = Livewire::test(CreateGuru::class)
            ->fillForm(dataGuru(['no_telepon' => $telepon, 'kelas' => []]))
            ->call('create')
            ->assertHasFormErrors(['kelas' => 'required']);

        $valid ? $test->assertHasNoFormErrors(['no_telepon']) : $test->assertHasFormErrors(['no_telepon' => 'regex']);
    })->with([
        ['081234567890', true],
        ['+6281234567890', true],
        ['021555123', false],
        ['08abc', false],
    ]);

    it('mewajibkan minimal satu Kelas aktif saat memulihkan Guru', function () {
        $guru = Guru::factory()->create();
        $guru->delete();
        $kelas = Kelas::factory()->count(2)->create();

        $daftarTrash = fn () => Livewire::test(ListGuru::class)->set('activeTab', 'trash');

        $daftarTrash()
            ->callAction(TestAction::make('restore')->table($guru), data: ['kelas' => []])
            ->assertHasActionErrors(['kelas' => 'required']);

        expect($guru->fresh()->trashed())->toBeTrue();

        $daftarTrash()
            ->callAction(TestAction::make('restore')->table($guru), data: ['kelas' => $kelas->modelKeys()])
            ->assertHasNoActionErrors();

        expect($guru->fresh()->trashed())->toBeFalse()
            ->and($guru->kelas()->count())->toBe(2);
    });
});

describe('State pagination', function () {
    it('mengingat halaman tabel saat kembali ke daftar', function () {
        Siswa::factory()->count(25)->create();

        Livewire::test(ListSiswa::class)->call('gotoPage', 3)->assertSet('paginators.page', 3);

        Livewire::test(ListSiswa::class)->assertSet('paginators.page', 3);
    });

    it('mengarahkan ke halaman terakhir yang valid bila halaman tersimpan tidak lagi ada', function () {
        $siswa = Siswa::factory()->count(21)->create();

        Livewire::test(ListSiswa::class)->call('gotoPage', 3)->assertSet('paginators.page', 3);

        $siswa->first()->delete();

        Livewire::test(ListSiswa::class)->assertSet('paginators.page', 2);
    });
});
