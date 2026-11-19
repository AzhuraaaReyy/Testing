<?php

namespace App\View\Components\Organisms;

use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * Konfirmasi logout (PRD 4.7). Admin keluar lewat rute Filament, Guru/Siswa lewat rute portal.
 */
final class LogoutDialog extends Component
{
    public readonly string $action;

    public function __construct(#[CurrentUser] User $user)
    {
        $this->action = route($user->role->logoutRoute());
    }

    public function render(): View
    {
        return view('components.organisms.logout-dialog');
    }
}
