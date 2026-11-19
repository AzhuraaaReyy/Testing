<?php

namespace App\View\Components\Organisms;

use App\DTOs\Navigation\NavGroupData;
use App\Models\User;
use App\Services\NavigationService;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\View\Component;

/**
 * Sidebar navigasi. Data menu disiapkan di sini (via NavigationService), bukan di Blade.
 */
final class Sidebar extends Component
{
    /** @var list<NavGroupData> */
    public readonly array $groups;

    public function __construct(NavigationService $navigation, Request $request, #[CurrentUser] User $user)
    {
        $this->groups = $navigation->groups($request, $user);
    }

    public function render(): View
    {
        return view('components.organisms.sidebar');
    }
}
