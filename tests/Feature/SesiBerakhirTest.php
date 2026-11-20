<?php

use App\Enums\JenisLaporan;
use App\Filament\Pages\Auth\Login;
use App\Http\PartialResponse;
use App\Models\User;
use App\Support\SesiLogin;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Cookie;
use Livewire\Livewire;

/*
| PRD 4.6 poin 3: session berakhir → diarahkan ke Login dengan pesan bahwa session telah berakhir.
| Pesan hanya untuk yang SEBELUMNYA login; tamu biasa dan logout disengaja tidak melihatnya.
*/

describe('penanda login', function () {
    it('dipasang saat login berhasil dan dihapus saat logout', function () {
        $user = User::factory()->siswa()->create();

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
            ->assertCookie(SesiLogin::COOKIE);

        $this->post(route('logout'))->assertCookieExpired(SesiLogin::COOKIE);
    });
});

describe('portal', function () {
    it('menampilkan pesan session berakhir di Login portal', function () {
        $this->withCookie(SesiLogin::COOKIE, '1')
            ->get(route('portal.dashboard'))
            ->assertRedirect(route('login'))
            ->assertSessionHas(SesiLogin::FLASH, SesiLogin::PESAN)
            ->assertCookieExpired(SesiLogin::COOKIE);

        $this->get(route('login'))->assertSeeText(SesiLogin::PESAN);
    });

    it('tidak menampilkan pesan untuk tamu yang belum pernah login', function () {
        $this->get(route('portal.dashboard'))
            ->assertRedirect(route('login'))
            ->assertSessionMissing(SesiLogin::FLASH);
    });

    it('menunda pesan pada permintaan parsial; frontend memuat ulang halaman', function () {
        $this->withCookie(SesiLogin::COOKIE, '1')
            // Header yang sama dengan resources/js/services/http.ts.
            ->withHeaders([PartialResponse::HEADER => PartialResponse::TABEL, 'X-Requested-With' => 'XMLHttpRequest'])
            ->get(route('portal.laporan.show', JenisLaporan::SiswaPerKelas))
            ->assertUnauthorized()
            ->assertSessionMissing(SesiLogin::FLASH);
    });
});

describe('panel Admin', function () {
    it('menampilkan notifikasi session berakhir di Login Admin', function () {
        $this->withCookie(SesiLogin::COOKIE, '1')
            ->get('/admin/siswa')
            ->assertRedirect(filament()->getLoginUrl());

        Notification::assertNotified(SesiLogin::PESAN);
    });

    it('tidak menampilkan notifikasi untuk tamu yang belum pernah login', function () {
        $this->get('/admin/siswa')->assertRedirect(filament()->getLoginUrl());

        Notification::assertNotNotified(SesiLogin::PESAN);
    });

    it('memasang penanda saat Admin login', function () {
        $admin = User::factory()->create();

        Livewire::test(Login::class)
            ->fillForm(['email' => $admin->email, 'password' => 'password'])
            ->call('authenticate');

        expect(collect(Cookie::getQueuedCookies())->map->getName())->toContain(SesiLogin::COOKIE);
    });
});
