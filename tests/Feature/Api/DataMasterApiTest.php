<?php

use App\Enums\JenisKelamin;
use App\Enums\Role;
use App\Enums\Tingkat;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

/*
| API CRUD Siswa/Kelas/Guru (khusus Admin). Aturan & pesan sama dengan panel Admin.
*/

beforeEach(fn () => Sanctum::actingAs(User::factory()->create()));

describe('akses', function () {
    it('menolak Guru/Siswa mengakses CRUD (403)', function (string $state) {
        Sanctum::actingAs(User::factory()->{$state}()->create());

        $this->getJson('/api/v1/siswa')->assertForbidden();
        $this->postJson('/api/v1/kelas', [])->assertForbidden();
    })->with(['guru', 'siswa']);
});

describe('Kelas', function () {
    it('membuat, membaca, mengubah, dan menghapus Kelas', function () {
        $id = $this->postJson('/api/v1/kelas', ['nama_kelas' => 'X IPA 1', 'tingkat' => 'X', 'jurusan' => 'IPA'])
            ->assertCreated()
            ->assertJsonPath('message', 'Data berhasil ditambahkan.')
            ->assertJsonPath('data.nama_kelas', 'X IPA 1')
            ->json('data.id');

        $this->getJson("/api/v1/kelas/{$id}")->assertOk()->assertJsonPath('data.tingkat', 'X');

        $this->putJson("/api/v1/kelas/{$id}", ['nama_kelas' => 'X IPA 1', 'tingkat' => 'X', 'keterangan' => 'Ruang 101'])
            ->assertOk()
            ->assertJsonPath('message', 'Perubahan berhasil disimpan.')
            ->assertJsonPath('data.keterangan', 'Ruang 101');

        $this->deleteJson("/api/v1/kelas/{$id}")
            ->assertOk()
            ->assertJsonPath('message', 'Data berhasil dipindahkan ke Trash.');

        expect(Kelas::onlyTrashed()->find($id))->not->toBeNull();
    });

    it('mengurutkan daftar Tingkat X, XI, XII dengan 10 data per halaman', function () {
        Kelas::factory()->create(['tingkat' => Tingkat::XII, 'nama_kelas' => 'XII A']);
        Kelas::factory()->create(['tingkat' => Tingkat::X, 'nama_kelas' => 'X A']);
        Kelas::factory()->count(10)->create(['tingkat' => Tingkat::XI]);

        $this->getJson('/api/v1/kelas')
            ->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('data.0.nama_kelas', 'X A')
            ->assertJsonPath('meta.total', 12)
            ->assertJsonPath('meta.per_page', 10);
    });

    it('menolak hapus Kelas yang masih dipakai dengan alasan (409)', function () {
        $kelas = Kelas::factory()->create();
        Siswa::factory()->for($kelas)->create();

        $this->deleteJson("/api/v1/kelas/{$kelas->id}")
            ->assertConflict()
            ->assertJsonPath('message', 'Kelas masih memiliki Siswa aktif.');
    });

    it('menolak Nama Kelas yang sudah dipakai, termasuk oleh Kelas di Trash (422)', function () {
        Kelas::factory()->create(['nama_kelas' => 'X IPA 1'])->delete();

        $this->postJson('/api/v1/kelas', ['nama_kelas' => 'X IPA 1', 'tingkat' => 'X'])
            ->assertUnprocessable()
            ->assertJsonPath('errors.nama_kelas.0', 'Nama Kelas sudah digunakan.');
    });

    it('mengizinkan Nama Kelas milik record yang sedang diubah', function () {
        $kelas = Kelas::factory()->create(['nama_kelas' => 'X IPA 1']);

        $this->putJson("/api/v1/kelas/{$kelas->id}", ['nama_kelas' => 'X IPA 1', 'tingkat' => 'XI'])->assertOk();
    });
});

describe('Siswa', function () {
    it('membuat Siswa beserta akun portal', function () {
        $kelas = Kelas::factory()->create();

        $this->postJson('/api/v1/siswa', [
            'nis' => '00123', 'nama' => 'Ani', 'jenis_kelamin' => 'P', 'alamat' => 'Jl. A', 'kelas_id' => $kelas->id,
            'akun' => ['email' => 'ani@sekolah.test', 'password' => 'rahasia123'],
        ])
            ->assertCreated()
            ->assertJsonPath('data.nis', '00123')
            ->assertJsonPath('data.kelas.id', $kelas->id)
            ->assertJsonPath('data.akun_email', 'ani@sekolah.test');

        expect(User::query()->where('email', 'ani@sekolah.test')->sole()->role)->toBe(Role::Siswa);
    });

    it('memvalidasi dengan pesan Bahasa Indonesia (422)', function () {
        $kelasTrash = Kelas::factory()->create();
        $kelasTrash->delete();

        $this->postJson('/api/v1/siswa', ['nis' => '', 'kelas_id' => $kelasTrash->id, 'akun' => ['email' => 'a@b.test']])
            ->assertUnprocessable()
            ->assertJsonPath('errors.nis.0', 'NIS wajib diisi.')
            ->assertJsonPath('errors.jenis_kelamin.0', 'Jenis Kelamin wajib diisi.')
            ->assertJsonPath('errors.kelas_id.0', 'Kelas yang dipilih tidak valid.')
            ->assertJsonValidationErrors(['akun.password' => 'Password wajib diisi.']);
    });

    it('memindahkan ke Trash, menolak mengubahnya, lalu memulihkan dengan Kelas wajib', function () {
        $siswa = Siswa::factory()->create();
        $kelasBaru = Kelas::factory()->create();

        $this->deleteJson("/api/v1/siswa/{$siswa->id}")->assertOk();

        $this->getJson('/api/v1/siswa?trash=1')->assertJsonPath('data.0.id', $siswa->id)->assertJsonPath('data.0.kelas', null);
        $this->getJson('/api/v1/siswa')->assertJsonCount(0, 'data');

        // Payload valid: yang diuji adalah aturan status (validasi berjalan lebih dulu, 422).
        $valid = ['nis' => $siswa->nis, 'nama' => 'X', 'jenis_kelamin' => 'L', 'alamat' => 'Y', 'kelas_id' => $kelasBaru->id];
        $this->putJson("/api/v1/siswa/{$siswa->id}", $valid)
            ->assertConflict()
            ->assertJsonPath('message', 'Data berada di Trash. Pulihkan terlebih dahulu.');

        $this->postJson("/api/v1/siswa/{$siswa->id}/pulihkan", [])
            ->assertUnprocessable()
            ->assertJsonPath('errors.kelas_id.0', 'Kelas wajib diisi.');

        $this->postJson("/api/v1/siswa/{$siswa->id}/pulihkan", ['kelas_id' => $kelasBaru->id])
            ->assertOk()
            ->assertJsonPath('message', 'Data berhasil dipulihkan.')
            ->assertJsonPath('data.kelas.id', $kelasBaru->id)
            ->assertJsonPath('data.dihapus_pada', null);
    });

    it('menolak memulihkan data yang tidak berada di Trash (409)', function () {
        $siswa = Siswa::factory()->create();

        $this->postJson("/api/v1/siswa/{$siswa->id}/pulihkan", ['kelas_id' => $siswa->kelas_id])
            ->assertConflict()
            ->assertJsonPath('message', 'Data tidak berada di Trash.');
    });

    it('mengembalikan 404 berbahasa Indonesia untuk data yang tidak ada', function () {
        $this->getJson('/api/v1/siswa/999999')
            ->assertNotFound()
            ->assertJsonPath('message', 'Data tidak ditemukan.');
    });
});

describe('Guru', function () {
    it('membuat Guru dengan banyak Kelas dan NIP kosong tersimpan NULL', function () {
        $kelas = Kelas::factory()->count(2)->create();

        $id = $this->postJson('/api/v1/guru', [
            'nip' => '', 'nama' => 'Budi', 'jenis_kelamin' => JenisKelamin::LakiLaki->value,
            'alamat' => 'Jl. B', 'no_telepon' => '+6281234567890', 'kelas' => $kelas->modelKeys(),
        ])
            ->assertCreated()
            ->assertJsonPath('data.nip', null)
            ->assertJsonCount(2, 'data.kelas')
            ->json('data.id');

        expect(Guru::query()->findOrFail($id)->kelas()->count())->toBe(2);
    });

    it('memvalidasi No. Telepon dan minimal satu Kelas', function () {
        $this->postJson('/api/v1/guru', ['nama' => 'Budi', 'no_telepon' => '021555', 'kelas' => []])
            ->assertUnprocessable()
            ->assertJsonPath('errors.no_telepon.0', 'Format No. Telepon tidak valid. Gunakan 08… atau +628….')
            ->assertJsonPath('errors.kelas.0', 'Kelas wajib diisi.');
    });

    it('melepas Kelas saat dihapus dan mewajibkan Kelas saat dipulihkan', function () {
        $guru = Guru::factory()->hasAttached(Kelas::factory()->count(2))->create();
        $kelas = Kelas::factory()->create();

        $this->deleteJson("/api/v1/guru/{$guru->id}")->assertOk();
        expect($guru->kelas()->count())->toBe(0);

        $this->postJson("/api/v1/guru/{$guru->id}/pulihkan", ['kelas' => []])->assertUnprocessable();

        $this->postJson("/api/v1/guru/{$guru->id}/pulihkan", ['kelas' => [$kelas->id]])
            ->assertOk()
            ->assertJsonPath('data.kelas.0.id', $kelas->id);
    });
});
