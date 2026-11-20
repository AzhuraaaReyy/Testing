<?php

namespace App\View\Components\Organisms;

use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * Dropdown akun portal (PRD 17.1): Nama, Email, Logout. Pengguna yang login diinjeksi oleh
 * container, sehingga view tidak mengambil data autentikasi sendiri.
 */
final class AccountMenu extends Component
{
    public function __construct(
        #[CurrentUser] public User $user,
    ) {}

    public function render(): View
    {
        return view('components.organisms.account-menu');
    }
}
