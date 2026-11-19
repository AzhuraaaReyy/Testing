<?php

namespace App\Http\Controllers;

use App\Actions\Dashboard\GetRingkasanSekolahAction;
use Illuminate\Contracts\View\View;

final class PortalDashboardController extends Controller
{
    public function __invoke(GetRingkasanSekolahAction $getRingkasan): View
    {
        return view('pages.portal.dashboard', [
            'ringkasan' => $getRingkasan->handle(),
        ]);
    }
}
