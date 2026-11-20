<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\AuthenticateUserAction;
use App\Actions\Auth\LogoutUserAction;
use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\PortalLoginRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

final class PortalLoginController extends Controller
{
    public function create(): View
    {
        return view('pages.auth.login');
    }

    public function store(PortalLoginRequest $request, AuthenticateUserAction $authenticate): RedirectResponse
    {
        $authenticate->handle($request->email(), $request->password(), (string) $request->ip(), Role::Guru, Role::Siswa);

        $request->session()->regenerate();

        // Kembali ke halaman yang sebelumnya diminta jika masih valid (PRD 4.6 poin 4).
        return redirect()->intended(route('portal.dashboard'));
    }

    public function destroy(LogoutUserAction $logout): RedirectResponse
    {
        $logout->handle();

        return redirect()->route('login')->with('status', LogoutUserAction::BERHASIL);
    }
}
