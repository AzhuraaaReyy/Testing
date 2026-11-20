<?php

namespace App\View\Components\Organisms;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * Konfirmasi logout portal Guru/Siswa (PRD 4.7). Logout Admin dikonfirmasi oleh user menu Filament.
 */
final class LogoutDialog extends Component
{
    public readonly string $action;

    public function __construct()
    {
        $this->action = route('logout');
    }

    public function render(): View
    {
        return view('components.organisms.logout-dialog');
    }
}
