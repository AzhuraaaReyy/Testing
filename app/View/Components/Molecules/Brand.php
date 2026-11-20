<?php

namespace App\View\Components\Molecules;

use App\Models\User;
use Illuminate\Container\Attributes\Config;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\View\Component;

/**
 * Identitas sistem di Navbar (PRD 17.1): logo jika tersedia, selain itu placeholder monogram.
 * Tautan mengarah ke beranda sesuai peran pengguna (tamu: halaman Login portal).
 * Dipakai juga sebagai logo panel Filament dengan :link="false" (Filament sudah membungkusnya dengan tautan).
 */
final class Brand extends Component
{
    public readonly string $homeUrl;

    // User diambil dari Request (bukan #[CurrentUser]) karena komponen ini juga tampil untuk tamu:
    // untuk tamu, #[CurrentUser] pada tipe nullable membuat objek User kosong, bukan null.
    public function __construct(
        Request $request,
        #[Config('sekolah.nama_sistem')] public string $namaSistem,
        #[Config('sekolah.nama_sekolah')] public ?string $namaSekolah = null,
        #[Config('sekolah.logo')] public ?string $logo = null,
        public bool $link = true,
    ) {
        /** @var User|null $user */
        $user = $request->user();
        $this->homeUrl = route($user?->role->homeRoute() ?? 'login');
    }

    public function render(): View
    {
        return view('components.molecules.brand');
    }
}
